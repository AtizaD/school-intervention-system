<?php
/**
 * Fee Class
 * Handles fee assignment and management
 */

class Fee {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Get all fees with student details
     */
    public function getAll($filters = []) {
        $sql = "SELECT sf.*, s.first_name, s.last_name, s.class, s.house
                FROM student_fees sf
                INNER JOIN students s ON sf.student_id = s.student_id";

        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "sf.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['class'])) {
            $where[] = "s.class = :class";
            $params[':class'] = $filters['class'];
        }

        if (!empty($filters['overdue'])) {
            $where[] = "sf.due_date < CURDATE() AND sf.status != 'paid'";
        }

        if (!empty($filters['search'])) {
            $where[] = "(s.student_id LIKE :search OR s.first_name LIKE :search OR s.last_name LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY sf.due_date ASC, s.first_name ASC";

        $this->db->query($sql);

        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }

        return $this->db->fetchAll();
    }

    /**
     * Get fee by ID
     */
    public function getById($id) {
        $this->db->query("SELECT sf.*, s.first_name, s.last_name, s.class, s.house,
                         u.fullname as created_by_name
                         FROM student_fees sf
                         INNER JOIN students s ON sf.student_id = s.student_id
                         INNER JOIN users u ON sf.created_by = u.user_id
                         WHERE sf.fee_id = :id");
        $this->db->bind(':id', $id);
        return $this->db->fetch();
    }

    /**
     * Get fee by student ID
     */
    public function getByStudentId($studentId) {
        $this->db->query("SELECT * FROM student_fees WHERE student_id = :student_id");
        $this->db->bind(':student_id', $studentId);
        return $this->db->fetch();
    }

    /**
     * Assign fee to student
     */
    public function assign($data) {
        // Validate input
        $validator = new Validator();
        $validator->required('student_id', $data['student_id'])
                  ->required('amount_due', $data['amount_due'])
                  ->numeric('amount_due', $data['amount_due'])
                  ->min('amount_due', $data['amount_due'], 0.01)
                  ->required('due_date', $data['due_date'])
                  ->date('due_date', $data['due_date']);

        if (!$validator->isValid()) {
            return [
                'success' => false,
                'message' => $validator->getFirstError(),
                'errors' => $validator->getErrors()
            ];
        }

        // Check if student exists
        $student = new Student();
        $studentData = $student->getById($data['student_id']);
        if (!$studentData) {
            return [
                'success' => false,
                'message' => 'Student not found.'
            ];
        }

        // Check if fee already assigned
        $existingFee = $this->getByStudentId($data['student_id']);
        if ($existingFee) {
            return [
                'success' => false,
                'message' => 'Fee already assigned to this student. Use update instead.'
            ];
        }

        // Insert fee
        $feeId = $this->db->insert('student_fees', [
            'student_id' => $data['student_id'],
            'amount_due' => $data['amount_due'],
            'amount_paid' => 0,
            'due_date' => $data['due_date'],
            'status' => FEE_PENDING,
            'created_by' => getCurrentUserId()
        ]);

        if ($feeId) {
            logActivity('fee_assign', 'student_fees', $feeId, null, $data);
            return [
                'success' => true,
                'message' => 'Fee assigned successfully.',
                'fee_id' => $feeId
            ];
        }

        return [
            'success' => false,
            'message' => ERR_DATABASE_ERROR
        ];
    }

    /**
     * Bulk assign fees to multiple students
     */
    public function bulkAssign($studentIds, $amount, $dueDate) {
        // Validate input
        if (empty($studentIds) || !is_array($studentIds)) {
            return [
                'success' => false,
                'message' => 'No students selected.'
            ];
        }

        $validator = new Validator();
        $validator->required('amount', $amount)
                  ->numeric('amount', $amount)
                  ->min('amount', $amount, 0.01)
                  ->required('due_date', $dueDate)
                  ->date('due_date', $dueDate);

        if (!$validator->isValid()) {
            return [
                'success' => false,
                'message' => $validator->getFirstError()
            ];
        }

        $this->db->beginTransaction();
        $successCount = 0;
        $failedStudents = [];

        try {
            foreach ($studentIds as $studentId) {
                // Check if fee already exists
                $existing = $this->getByStudentId($studentId);
                if ($existing) {
                    $failedStudents[] = $studentId . ' (already has fee)';
                    continue;
                }

                // Insert fee
                $feeId = $this->db->insert('student_fees', [
                    'student_id' => $studentId,
                    'amount_due' => $amount,
                    'amount_paid' => 0,
                    'due_date' => $dueDate,
                    'status' => FEE_PENDING,
                    'created_by' => getCurrentUserId()
                ]);

                if ($feeId) {
                    $successCount++;
                    logActivity('fee_bulk_assign', 'student_fees', $feeId);
                } else {
                    $failedStudents[] = $studentId;
                }
            }

            $this->db->commit();

            $message = "$successCount fee(s) assigned successfully.";
            if (!empty($failedStudents)) {
                $message .= " Failed: " . implode(', ', $failedStudents);
            }

            return [
                'success' => true,
                'message' => $message,
                'success_count' => $successCount,
                'failed_students' => $failedStudents
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Bulk assign error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred during bulk assignment.'
            ];
        }
    }

    /**
     * Update fee
     */
    public function update($id, $data) {
        // Validate input
        $validator = new Validator();
        $validator->required('amount_due', $data['amount_due'])
                  ->numeric('amount_due', $data['amount_due'])
                  ->min('amount_due', $data['amount_due'], 0.01)
                  ->required('due_date', $data['due_date'])
                  ->date('due_date', $data['due_date']);

        if (!$validator->isValid()) {
            return [
                'success' => false,
                'message' => $validator->getFirstError()
            ];
        }

        // Get old values
        $oldData = $this->getById($id);

        // Update fee
        $updated = $this->db->update('student_fees', [
            'amount_due' => $data['amount_due'],
            'due_date' => $data['due_date']
        ], 'fee_id = :id', [':id' => $id]);

        if ($updated) {
            // Update status based on balance
            $this->updateFeeStatus($id);
            logActivity('update', 'student_fees', $id, $oldData, $data);
            return [
                'success' => true,
                'message' => MSG_UPDATE_SUCCESS
            ];
        }

        return [
            'success' => false,
            'message' => ERR_DATABASE_ERROR
        ];
    }

    /**
     * Update fee status based on payment
     */
    public function updateFeeStatus($feeId) {
        $fee = $this->getById($feeId);
        if (!$fee) return;

        $balance = $fee['amount_due'] - $fee['amount_paid'];
        $status = FEE_PENDING;

        if ($balance <= 0) {
            $status = FEE_PAID;
        } elseif ($fee['amount_paid'] > 0) {
            $status = FEE_PARTIAL;
        } elseif (strtotime($fee['due_date']) < time()) {
            $status = FEE_OVERDUE;
        }

        $this->db->update('student_fees',
            ['status' => $status],
            'fee_id = :id',
            [':id' => $feeId]
        );
    }

    /**
     * Update all overdue fees
     */
    public function updateOverdueFees() {
        $this->db->query("UPDATE student_fees
                         SET status = :overdue
                         WHERE due_date < CURDATE()
                         AND status NOT IN (:paid, :partial)");
        $this->db->bind(':overdue', FEE_OVERDUE);
        $this->db->bind(':paid', FEE_PAID);
        $this->db->bind(':partial', FEE_PARTIAL);
        return $this->db->execute();
    }

    /**
     * Get fee statistics
     */
    public function getStats() {
        $this->db->query("SELECT
                         COUNT(*) as total_fees,
                         SUM(amount_due) as total_due,
                         SUM(amount_paid) as total_paid,
                         SUM(balance) as total_balance,
                         SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_count,
                         SUM(CASE WHEN status = 'partial' THEN 1 ELSE 0 END) as partial_count,
                         SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                         SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) as overdue_count
                         FROM student_fees");

        return $this->db->fetch();
    }

    /**
     * Get overdue fees
     */
    public function getOverdue() {
        return $this->getAll(['overdue' => true]);
    }
}
