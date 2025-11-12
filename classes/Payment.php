<?php
/**
 * Payment Class
 * Handles payment recording and management
 */

class Payment {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Get all payments with filters
     */
    public function getAll($filters = []) {
        $sql = "SELECT p.*, s.first_name, s.last_name, s.class,
                u.fullname as received_by_name
                FROM payments p
                INNER JOIN students s ON p.student_id = s.student_id
                INNER JOIN users u ON p.received_by = u.user_id";

        $where = [];
        $params = [];

        if (!empty($filters['student_id'])) {
            $where[] = "p.student_id = :student_id";
            $params[':student_id'] = $filters['student_id'];
        }

        if (!empty($filters['payment_method'])) {
            $where[] = "p.payment_method = :payment_method";
            $params[':payment_method'] = $filters['payment_method'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = "p.payment_date >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "p.payment_date <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(p.receipt_number LIKE :search OR s.first_name LIKE :search OR s.last_name LIKE :search OR s.student_id LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['received_by'])) {
            $where[] = "p.received_by = :received_by";
            $params[':received_by'] = $filters['received_by'];
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY p.payment_date DESC, p.created_at DESC";

        $this->db->query($sql);

        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }

        return $this->db->fetchAll();
    }

    /**
     * Get payment by ID
     */
    public function getById($id) {
        $this->db->query("SELECT p.*, s.first_name, s.last_name, s.class, s.house,
                         u.fullname as received_by_name, u.contact as received_by_contact,
                         sf.amount_due, sf.amount_paid, sf.balance
                         FROM payments p
                         INNER JOIN students s ON p.student_id = s.student_id
                         INNER JOIN users u ON p.received_by = u.user_id
                         INNER JOIN student_fees sf ON p.fee_id = sf.fee_id
                         WHERE p.payment_id = :id");
        $this->db->bind(':id', $id);
        return $this->db->fetch();
    }

    /**
     * Get payment by receipt number
     */
    public function getByReceiptNumber($receiptNumber) {
        $this->db->query("SELECT p.*, s.first_name, s.last_name, s.class, s.house,
                         u.fullname as received_by_name, u.contact as received_by_contact,
                         sf.amount_due, sf.amount_paid as total_paid, sf.balance
                         FROM payments p
                         INNER JOIN students s ON p.student_id = s.student_id
                         INNER JOIN users u ON p.received_by = u.user_id
                         INNER JOIN student_fees sf ON p.fee_id = sf.fee_id
                         WHERE p.receipt_number = :receipt");
        $this->db->bind(':receipt', $receiptNumber);
        return $this->db->fetch();
    }

    /**
     * Record new payment
     */
    public function record($data) {
        // Validate input
        $validator = new Validator();
        $validator->required('student_id', $data['student_id'])
                  ->required('amount_paid', $data['amount_paid'])
                  ->numeric('amount_paid', $data['amount_paid'])
                  ->min('amount_paid', $data['amount_paid'], 0.01)
                  ->required('payment_method', $data['payment_method'])
                  ->inArray('payment_method', $data['payment_method'], [PAYMENT_CASH, PAYMENT_MOBILE_MONEY, PAYMENT_OTHERS])
                  ->required('payment_date', $data['payment_date'])
                  ->date('payment_date', $data['payment_date']);

        if (!$validator->isValid()) {
            return [
                'success' => false,
                'message' => $validator->getFirstError(),
                'errors' => $validator->getErrors()
            ];
        }

        // Get student fee
        $fee = new Fee();
        $studentFee = $fee->getByStudentId($data['student_id']);

        if (!$studentFee) {
            return [
                'success' => false,
                'message' => 'No fee assigned to this student.'
            ];
        }

        // Check if payment exceeds balance
        $balance = $studentFee['amount_due'] - $studentFee['amount_paid'];
        if ($data['amount_paid'] > $balance) {
            return [
                'success' => false,
                'message' => "Payment amount (" . formatCurrency($data['amount_paid']) . ") exceeds balance (" . formatCurrency($balance) . ")."
            ];
        }

        $this->db->beginTransaction();

        try {
            // Generate receipt number
            $receiptNumber = $this->generateReceiptNumber();

            // Insert payment
            $paymentId = $this->db->insert('payments', [
                'fee_id' => $studentFee['fee_id'],
                'student_id' => $data['student_id'],
                'amount_paid' => $data['amount_paid'],
                'payment_method' => $data['payment_method'],
                'reference_number' => sanitize($data['reference_number'] ?? ''),
                'payment_date' => $data['payment_date'],
                'received_by' => getCurrentUserId(),
                'receipt_number' => $receiptNumber,
                'notes' => sanitize($data['notes'] ?? '')
            ]);

            if (!$paymentId) {
                throw new Exception('Failed to insert payment');
            }

            // Update student fee amount paid
            $newAmountPaid = $studentFee['amount_paid'] + $data['amount_paid'];
            $this->db->update('student_fees',
                ['amount_paid' => $newAmountPaid],
                'fee_id = :id',
                [':id' => $studentFee['fee_id']]
            );

            // Update fee status
            $fee->updateFeeStatus($studentFee['fee_id']);

            $this->db->commit();

            logActivity('payment_record', 'payments', $paymentId, null, $data);

            return [
                'success' => true,
                'message' => MSG_PAYMENT_SUCCESS,
                'payment_id' => $paymentId,
                'receipt_number' => $receiptNumber
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Payment error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to record payment. Please try again.'
            ];
        }
    }

    /**
     * Generate unique receipt number
     */
    private function generateReceiptNumber() {
        $prefix = 'RCP';
        $date = date('Ymd');
        $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $receiptNumber = $prefix . $date . $random;

        // Check if exists (unlikely but just in case)
        $existing = $this->getByReceiptNumber($receiptNumber);
        if ($existing) {
            // Recursive call with new random number
            return $this->generateReceiptNumber();
        }

        return $receiptNumber;
    }

    /**
     * Delete payment (admin only, use with caution)
     */
    public function delete($id) {
        // Get payment details
        $payment = $this->getById($id);
        if (!$payment) {
            return [
                'success' => false,
                'message' => ERR_NOT_FOUND
            ];
        }

        $this->db->beginTransaction();

        try {
            // Delete payment
            $this->db->delete('payments', 'payment_id = :id', [':id' => $id]);

            // Update student fee amount paid
            $newAmountPaid = $payment['amount_paid'] - $payment['amount_paid'];
            $this->db->update('student_fees',
                ['amount_paid' => $newAmountPaid],
                'fee_id = :id',
                [':id' => $payment['fee_id']]
            );

            // Update fee status
            $fee = new Fee();
            $fee->updateFeeStatus($payment['fee_id']);

            $this->db->commit();

            logActivity('payment_delete', 'payments', $id, $payment);

            return [
                'success' => true,
                'message' => MSG_DELETE_SUCCESS
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Delete payment error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to delete payment.'
            ];
        }
    }

    /**
     * Get payment statistics
     */
    public function getStats($filters = []) {
        $sql = "SELECT
                COUNT(*) as total_payments,
                SUM(amount_paid) as total_amount,
                SUM(CASE WHEN payment_method = 'cash' THEN amount_paid ELSE 0 END) as cash_amount,
                SUM(CASE WHEN payment_method = 'mobile_money' THEN amount_paid ELSE 0 END) as mobile_money_amount,
                SUM(CASE WHEN payment_method = 'others' THEN amount_paid ELSE 0 END) as others_amount
                FROM payments WHERE 1=1";

        $params = [];

        if (!empty($filters['date_from'])) {
            $sql .= " AND payment_date >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND payment_date <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        if (!empty($filters['received_by'])) {
            $sql .= " AND received_by = :received_by";
            $params[':received_by'] = $filters['received_by'];
        }

        $this->db->query($sql);

        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }

        return $this->db->fetch();
    }

    /**
     * Get daily collection summary
     */
    public function getDailySummary($date = null) {
        $date = $date ?? date('Y-m-d');

        $this->db->query("SELECT
                         COUNT(*) as total_payments,
                         SUM(amount_paid) as total_collected,
                         payment_method,
                         u.fullname as received_by_name
                         FROM payments p
                         INNER JOIN users u ON p.received_by = u.user_id
                         WHERE p.payment_date = :date
                         GROUP BY p.payment_method, u.fullname
                         ORDER BY u.fullname, p.payment_method");
        $this->db->bind(':date', $date);

        return $this->db->fetchAll();
    }

    /**
     * Get payments by student
     */
    public function getByStudent($studentId) {
        return $this->getAll(['student_id' => $studentId]);
    }
}
