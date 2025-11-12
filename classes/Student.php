<?php
/**
 * Student Class
 * Handles student management operations
 */

class Student {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Get all students with filters
     */
    public function getAll($filters = []) {
        $sql = "SELECT s.*,
                COALESCE(sf.amount_due, 0) as amount_due,
                COALESCE(sf.amount_paid, 0) as amount_paid,
                COALESCE(sf.balance, 0) as balance,
                sf.status as fee_status
                FROM students s
                LEFT JOIN student_fees sf ON s.student_id = sf.student_id";

        $where = [];
        $params = [];

        if (!empty($filters['class'])) {
            $where[] = "s.class = :class";
            $params[':class'] = $filters['class'];
        }

        if (!empty($filters['house'])) {
            $where[] = "s.house = :house";
            $params[':house'] = $filters['house'];
        }

        if (!empty($filters['gender'])) {
            $where[] = "s.gender = :gender";
            $params[':gender'] = $filters['gender'];
        }

        if (isset($filters['is_active'])) {
            $where[] = "s.is_active = :is_active";
            $params[':is_active'] = $filters['is_active'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(s.student_id LIKE :search OR s.first_name LIKE :search OR s.last_name LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY s.first_name ASC, s.last_name ASC";

        $this->db->query($sql);

        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }

        return $this->db->fetchAll();
    }

    /**
     * Get student by ID
     */
    public function getById($id) {
        $this->db->query("SELECT s.*,
                         COALESCE(sf.amount_due, 0) as amount_due,
                         COALESCE(sf.amount_paid, 0) as amount_paid,
                         COALESCE(sf.balance, 0) as balance,
                         sf.status as fee_status,
                         sf.due_date
                         FROM students s
                         LEFT JOIN student_fees sf ON s.student_id = sf.student_id
                         WHERE s.student_id = :id");
        $this->db->bind(':id', $id);
        return $this->db->fetch();
    }

    /**
     * Get student with parents
     */
    public function getWithParents($id) {
        $student = $this->getById($id);

        if ($student) {
            // Get parents
            $this->db->query("SELECT p.*, sp.is_primary
                             FROM parents p
                             INNER JOIN student_parents sp ON p.parent_id = sp.parent_id
                             WHERE sp.student_id = :student_id
                             ORDER BY sp.is_primary DESC, p.fullname ASC");
            $this->db->bind(':student_id', $id);
            $student['parents'] = $this->db->fetchAll();
        }

        return $student;
    }

    /**
     * Generate unique student ID (username)
     * Format: {CLASS}-{INITIALS}{3-DIGIT-SEQUENCE}
     * Example: 1A-JD001, 2B-MKA002, 3C-TS003
     *
     * IMPORTANT: This uses the EXACT same logic as the Assessment system
     * to ensure consistent student IDs/usernames across both systems.
     * The student_id IS the username - there is no separate admission number.
     */
    public function generateUsername($firstName, $lastName, $class) {
        // Check if a student with the same name already exists in this class
        // This matches the assessment system's duplicate detection logic
        $this->db->query("SELECT student_id FROM students
                         WHERE (
                            (LOWER(first_name) = LOWER(:fname1) AND LOWER(last_name) = LOWER(:lname1))
                            OR
                            (LOWER(first_name) = LOWER(:fname2) AND LOWER(last_name) = LOWER(:lname2))
                         )
                         AND class = :class");
        $this->db->bind(':fname1', strtolower($firstName));
        $this->db->bind(':lname1', strtolower($lastName));
        $this->db->bind(':fname2', strtolower($lastName)); // Swapped order check
        $this->db->bind(':lname2', strtolower($firstName)); // Swapped order check
        $this->db->bind(':class', $class);

        $existingStudent = $this->db->fetch();
        if ($existingStudent) {
            return [
                'success' => false,
                'message' => "A student with this name is already registered in this class. Username: {$existingStudent['student_id']}. Please contact your administrator if this is not correct."
            ];
        }

        // Generate base username from class name and initials
        // This EXACTLY matches the assessment system logic (lines 106-123)
        $nameParts = array_merge(
            explode(' ', $firstName),
            explode(' ', $lastName)
        );

        // Clean up name parts
        array_walk($nameParts, function(&$part) {
            $part = trim($part);
        });
        $nameParts = array_filter($nameParts);
        sort($nameParts, SORT_STRING | SORT_FLAG_CASE);

        // Generate initials from sorted names
        $initials = '';
        foreach ($nameParts as $part) {
            $initials .= substr($part, 0, 1);
        }
        $initials = strtoupper($initials);

        // Create base username
        $baseUsername = trim($class) . '-' . $initials;

        // Find the highest existing suffix for this base username
        // This matches assessment system lines 126-133
        $this->db->query("SELECT student_id FROM students
                         WHERE student_id LIKE :base
                         ORDER BY CAST(SUBSTRING(student_id, -3) AS UNSIGNED) DESC
                         LIMIT 1");
        $this->db->bind(':base', $baseUsername . '%');
        $lastUsername = $this->db->fetch();

        // Determine the next suffix to use
        // This matches assessment system lines 136-142
        if ($lastUsername) {
            // Extract the numeric suffix and increment it
            preg_match('/(\d+)$/', $lastUsername['student_id'], $matches);
            $suffix = isset($matches[1]) ? (intval($matches[1]) + 1) : 1;
        } else {
            $suffix = 1;
        }

        $username = $baseUsername . str_pad($suffix, 3, '0', STR_PAD_LEFT);

        // Double check that this username doesn't already exist (extra safety check)
        // This matches assessment system lines 146-169
        $this->db->query("SELECT student_id FROM students WHERE student_id = :id");
        $this->db->bind(':id', $username);

        if ($this->db->fetch()) {
            // This should rarely happen if our suffix calculation is correct,
            // but as a fallback, we'll keep incrementing until we find an unused username
            $counter = $suffix + 1;
            $isUnique = false;

            while (!$isUnique && $counter < 1000) { // Set a reasonable limit
                $candidateUsername = $baseUsername . str_pad($counter, 3, '0', STR_PAD_LEFT);
                $this->db->query("SELECT student_id FROM students WHERE student_id = :id");
                $this->db->bind(':id', $candidateUsername);

                if (!$this->db->fetch()) {
                    $username = $candidateUsername;
                    $isUnique = true;
                }
                $counter++;
            }

            if (!$isUnique) {
                return [
                    'success' => false,
                    'message' => 'Unable to generate a unique username. Please contact support.'
                ];
            }
        }

        return [
            'success' => true,
            'username' => $username
        ];
    }

    /**
     * Create new student
     */
    public function create($data) {
        // Validate input
        $validator = new Validator();
        $validator->required('first_name', $data['first_name'])
                  ->minLength('first_name', $data['first_name'], 2)
                  ->required('last_name', $data['last_name'])
                  ->minLength('last_name', $data['last_name'], 2)
                  ->required('gender', $data['gender'])
                  ->inArray('gender', $data['gender'], [GENDER_MALE, GENDER_FEMALE])
                  ->required('class', $data['class'])
                  ->required('house', $data['house'])
                  ->inArray('house', $data['house'], [HOUSE_1, HOUSE_2, HOUSE_3, HOUSE_4]);

        if (!$validator->isValid()) {
            return [
                'success' => false,
                'message' => $validator->getFirstError(),
                'errors' => $validator->getErrors()
            ];
        }

        // Auto-generate student ID (username) if not provided
        if (empty($data['student_id'])) {
            $usernameResult = $this->generateUsername(
                $data['first_name'],
                $data['last_name'],
                $data['class']
            );

            if (!$usernameResult['success']) {
                return $usernameResult;
            }

            $data['student_id'] = $usernameResult['username'];
        } else {
            // If student_id is provided, check if it's unique
            $validator->unique('student_id', $data['student_id'], 'students', 'student_id');
            if (!$validator->isValid()) {
                return [
                    'success' => false,
                    'message' => 'Student ID/Username already exists',
                    'errors' => $validator->getErrors()
                ];
            }
        }

        // Properly case names: First letter uppercase, rest lowercase
        $firstName = ucwords(strtolower(trim($data['first_name'])));
        $lastName = ucwords(strtolower(trim($data['last_name'])));

        // Insert student
        $insertId = $this->db->insert('students', [
            'student_id' => sanitize($data['student_id']),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'gender' => $data['gender'],
            'class' => sanitize($data['class']),
            'house' => $data['house'],
            'is_active' => 1
        ]);

        // Check if insert succeeded (returns auto-increment ID on success, false on failure)
        if ($insertId) {
            logActivity('CREATE', 'students', $data['student_id'], null, [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'class' => $data['class']
            ]);

            // Automatically assign default fee amount to new student
            $this->assignDefaultFee($data['student_id']);

            return [
                'success' => true,
                'message' => MSG_CREATE_SUCCESS,
                'student_id' => $data['student_id'],
                'full_name' => $firstName . ' ' . $lastName
            ];
        }

        return [
            'success' => false,
            'message' => ERR_DATABASE_ERROR
        ];
    }

    /**
     * Assign default fee amount to student
     * Called automatically when a new student is created
     */
    private function assignDefaultFee($studentId) {
        try {
            // Get default fee amount from settings
            $defaultAmount = getSetting('default_student_fee_amount', 500.00);
            $dueDays = getSetting('default_fee_due_days', 30);

            // Calculate due date
            $dueDate = date('Y-m-d', strtotime("+{$dueDays} days"));

            // Get current user ID (who created the student)
            $createdBy = getCurrentUserId();

            // Insert fee record
            $this->db->insert('student_fees', [
                'student_id' => $studentId,
                'amount_due' => $defaultAmount,
                'amount_paid' => 0.00,
                'due_date' => $dueDate,
                'status' => 'pending',
                'created_by' => $createdBy
            ]);

            // Log the fee assignment
            logActivity('CREATE', 'student_fees', $studentId, null, [
                'amount_due' => $defaultAmount,
                'due_date' => $dueDate
            ]);

            return true;
        } catch (Exception $e) {
            error_log("Failed to assign default fee to student {$studentId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update student
     */
    public function update($id, $data) {
        // Validate input
        $validator = new Validator();
        $validator->required('first_name', $data['first_name'])
                  ->minLength('first_name', $data['first_name'], 2)
                  ->required('last_name', $data['last_name'])
                  ->minLength('last_name', $data['last_name'], 2)
                  ->required('gender', $data['gender'])
                  ->inArray('gender', $data['gender'], [GENDER_MALE, GENDER_FEMALE])
                  ->required('class', $data['class'])
                  ->required('house', $data['house'])
                  ->inArray('house', $data['house'], [HOUSE_1, HOUSE_2, HOUSE_3, HOUSE_4]);

        if (!$validator->isValid()) {
            return [
                'success' => false,
                'message' => $validator->getFirstError(),
                'errors' => $validator->getErrors()
            ];
        }

        // Get old values for audit
        $oldData = $this->getById($id);

        // Update student
        $updated = $this->db->update('students', [
            'first_name' => sanitize($data['first_name']),
            'last_name' => sanitize($data['last_name']),
            'gender' => $data['gender'],
            'class' => sanitize($data['class']),
            'house' => $data['house']
        ], 'student_id = :id', [':id' => $id]);

        if ($updated) {
            logActivity('update', 'students', $id, $oldData, $data);
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
     * Toggle student active status
     */
    public function toggleStatus($id) {
        $student = $this->getById($id);
        if (!$student) {
            return [
                'success' => false,
                'message' => ERR_NOT_FOUND
            ];
        }

        $newStatus = $student['is_active'] ? 0 : 1;

        $updated = $this->db->update('students',
            ['is_active' => $newStatus],
            'student_id = :id',
            [':id' => $id]
        );

        if ($updated) {
            logActivity('status_change', 'students', $id, ['is_active' => $student['is_active']], ['is_active' => $newStatus]);
            return [
                'success' => true,
                'message' => $newStatus ? 'Student activated successfully.' : 'Student deactivated successfully.'
            ];
        }

        return [
            'success' => false,
            'message' => ERR_DATABASE_ERROR
        ];
    }

    /**
     * Delete student
     */
    public function delete($id) {
        // Check if student has payments
        $this->db->query("SELECT COUNT(*) as count FROM payments WHERE student_id = :id");
        $this->db->bind(':id', $id);
        $result = $this->db->fetch();

        if ($result['count'] > 0) {
            return [
                'success' => false,
                'message' => 'Cannot delete student with existing payment records.'
            ];
        }

        // Get student data for audit
        $studentData = $this->getById($id);

        $deleted = $this->db->delete('students', 'student_id = :id', [':id' => $id]);

        if ($deleted) {
            logActivity('delete', 'students', $id, $studentData);
            return [
                'success' => true,
                'message' => MSG_DELETE_SUCCESS
            ];
        }

        return [
            'success' => false,
            'message' => ERR_DATABASE_ERROR
        ];
    }

    /**
     * Get student statistics
     */
    public function getStats() {
        $this->db->query("SELECT
                         COUNT(*) as total,
                         SUM(CASE WHEN gender = 'male' THEN 1 ELSE 0 END) as males,
                         SUM(CASE WHEN gender = 'female' THEN 1 ELSE 0 END) as females,
                         SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active
                         FROM students");

        return $this->db->fetch();
    }

    /**
     * Get students by class
     */
    public function getByClass($class) {
        return $this->getAll(['class' => $class]);
    }

    /**
     * Get students by house
     */
    public function getByHouse($house) {
        return $this->getAll(['house' => $house]);
    }
}
