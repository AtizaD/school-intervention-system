<?php
/**
 * Notification Class
 * Handles notification management and tracking
 */

class Notification {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Create notification record
     */
    public function create($data) {
        $notificationId = $this->db->insert('notifications', [
            'recipient_type' => $data['recipient_type'], // 'parent' or 'student'
            'recipient_id' => $data['recipient_id'],
            'notification_type' => $data['notification_type'], // 'sms', 'email', etc.
            'subject' => isset($data['subject']) ? sanitize($data['subject']) : null,
            'message' => sanitize($data['message']),
            'status' => 'pending'
        ]);

        if ($notificationId) {
            return [
                'success' => true,
                'notification_id' => $notificationId
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to create notification record'
        ];
    }

    /**
     * Update notification status
     */
    public function updateStatus($id, $status, $response = null) {
        $updateData = [
            'status' => $status,
            'sent_at' => date('Y-m-d H:i:s')
        ];

        if ($response) {
            $updateData['response'] = $response;
        }

        $updated = $this->db->update('notifications',
            $updateData,
            'notification_id = :id',
            [':id' => $id]
        );

        return $updated;
    }

    /**
     * Get notifications by recipient
     */
    public function getByRecipient($recipientType, $recipientId, $limit = 50) {
        $this->db->query("SELECT * FROM notifications
                         WHERE recipient_type = :type AND recipient_id = :id
                         ORDER BY created_at DESC
                         LIMIT :limit");
        $this->db->bind(':type', $recipientType);
        $this->db->bind(':id', $recipientId);
        $this->db->bind(':limit', $limit);

        return $this->db->fetchAll();
    }

    /**
     * Get pending notifications
     */
    public function getPending($type = null) {
        $sql = "SELECT * FROM notifications WHERE status = 'pending'";

        if ($type) {
            $sql .= " AND notification_type = :type";
        }

        $sql .= " ORDER BY created_at ASC LIMIT 100";

        $this->db->query($sql);

        if ($type) {
            $this->db->bind(':type', $type);
        }

        return $this->db->fetchAll();
    }

    /**
     * Get notification statistics
     */
    public function getStats($dateFrom = null, $dateTo = null) {
        $sql = "SELECT
                notification_type,
                status,
                COUNT(*) as count
                FROM notifications";

        $conditions = [];
        $params = [];

        if ($dateFrom) {
            $conditions[] = "created_at >= :date_from";
            $params[':date_from'] = $dateFrom;
        }

        if ($dateTo) {
            $conditions[] = "created_at <= :date_to";
            $params[':date_to'] = $dateTo;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " GROUP BY notification_type, status";

        $this->db->query($sql);

        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }

        return $this->db->fetchAll();
    }

    /**
     * Send fee reminder to parent
     */
    public function sendFeeReminder($parentId, $studentName, $amountDue, $dueDate) {
        $parent = (new ParentModel())->getById($parentId);

        if (!$parent) {
            return [
                'success' => false,
                'message' => 'Parent not found'
            ];
        }

        $message = "INTERVENTION SCHOOL: Dear {$parent['fullname']}, {$studentName} has an outstanding fee balance of GHS " . number_format($amountDue, 2) . " due on " . date('d/m/Y', strtotime($dueDate)) . ". Please make payment. Thank you.";

        // Create notification record
        $notification = $this->create([
            'recipient_type' => 'parent',
            'recipient_id' => $parentId,
            'notification_type' => 'sms',
            'subject' => 'Fee Reminder',
            'message' => $message
        ]);

        if (!$notification['success']) {
            return $notification;
        }

        // Send SMS
        $sms = new SMS();
        $result = $sms->send($parent['contact'], $message);

        // Update notification status
        $this->updateStatus(
            $notification['notification_id'],
            $result['success'] ? 'sent' : 'failed',
            $result['message']
        );

        return $result;
    }

    /**
     * Send payment receipt notification
     */
    public function sendPaymentReceipt($parentId, $studentName, $amount, $receiptNo) {
        $parent = (new ParentModel())->getById($parentId);

        if (!$parent) {
            return [
                'success' => false,
                'message' => 'Parent not found'
            ];
        }

        $message = "INTERVENTION SCHOOL: Payment of GHS " . number_format($amount, 2) . " received for {$studentName}. Receipt No: {$receiptNo}. Thank you.";

        // Create notification record
        $notification = $this->create([
            'recipient_type' => 'parent',
            'recipient_id' => $parentId,
            'notification_type' => 'sms',
            'subject' => 'Payment Receipt',
            'message' => $message
        ]);

        if (!$notification['success']) {
            return $notification;
        }

        // Send SMS
        $sms = new SMS();
        $result = $sms->send($parent['contact'], $message);

        // Update notification status
        $this->updateStatus(
            $notification['notification_id'],
            $result['success'] ? 'sent' : 'failed',
            $result['message']
        );

        return $result;
    }
}
