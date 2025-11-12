<?php
/**
 * User Class
 * Handles user management operations
 */

class User {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Get all users
     */
    public function getAll($role = null) {
        $sql = "SELECT user_id, fullname, contact, role, is_active, created_at, last_login
                FROM users";

        if ($role) {
            $sql .= " WHERE role = :role";
        }

        $sql .= " ORDER BY fullname ASC";

        $this->db->query($sql);

        if ($role) {
            $this->db->bind(':role', $role);
        }

        return $this->db->fetchAll();
    }

    /**
     * Get user by ID
     */
    public function getById($id) {
        $this->db->query("SELECT user_id, fullname, contact, role, is_active, created_at, last_login
                         FROM users WHERE user_id = :id");
        $this->db->bind(':id', $id);
        return $this->db->fetch();
    }

    /**
     * Get user by contact
     */
    public function getByContact($contact) {
        $this->db->query("SELECT * FROM users WHERE contact = :contact");
        $this->db->bind(':contact', $contact);
        return $this->db->fetch();
    }

    /**
     * Create new user
     */
    public function create($data) {
        // Validate input
        $validator = new Validator();
        $validator->required('fullname', $data['fullname'])
                  ->minLength('fullname', $data['fullname'], 3)
                  ->required('contact', $data['contact'])
                  ->phone('contact', $data['contact'])
                  ->unique('contact', $data['contact'], 'users', 'contact')
                  ->required('password', $data['password'])
                  ->password('password', $data['password'])
                  ->required('role', $data['role'])
                  ->inArray('role', $data['role'], [ROLE_ADMIN, ROLE_TEACHER]);

        if (!$validator->isValid()) {
            return [
                'success' => false,
                'message' => $validator->getFirstError(),
                'errors' => $validator->getErrors()
            ];
        }

        // Hash password
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

        // Insert user
        $userId = $this->db->insert('users', [
            'fullname' => sanitize($data['fullname']),
            'contact' => sanitize($data['contact']),
            'password_hash' => $passwordHash,
            'role' => $data['role'],
            'is_active' => 1
        ]);

        if ($userId) {
            logActivity('create', 'users', $userId, null, $data);
            return [
                'success' => true,
                'message' => MSG_CREATE_SUCCESS,
                'user_id' => $userId
            ];
        }

        return [
            'success' => false,
            'message' => ERR_DATABASE_ERROR
        ];
    }

    /**
     * Update user
     */
    public function update($id, $data) {
        // Validate input
        $validator = new Validator();
        $validator->required('fullname', $data['fullname'])
                  ->minLength('fullname', $data['fullname'], 3)
                  ->required('contact', $data['contact'])
                  ->phone('contact', $data['contact'])
                  ->required('role', $data['role'])
                  ->inArray('role', $data['role'], [ROLE_ADMIN, ROLE_TEACHER]);

        if (!$validator->isValid()) {
            return [
                'success' => false,
                'message' => $validator->getFirstError(),
                'errors' => $validator->getErrors()
            ];
        }

        // Check if contact is unique (excluding current user)
        $existingUser = $this->getByContact($data['contact']);
        if ($existingUser && $existingUser['user_id'] != $id) {
            return [
                'success' => false,
                'message' => 'Contact number already in use.'
            ];
        }

        // Get old values for audit
        $oldData = $this->getById($id);

        // Prepare update data
        $updateData = [
            'fullname' => sanitize($data['fullname']),
            'contact' => sanitize($data['contact']),
            'role' => $data['role']
        ];

        // Update password if provided
        if (!empty($data['password'])) {
            $validator->password('password', $data['password']);
            if (!$validator->isValid()) {
                return [
                    'success' => false,
                    'message' => $validator->getFirstError(),
                    'errors' => $validator->getErrors()
                ];
            }
            $updateData['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        // Update user
        $updated = $this->db->update('users',
            $updateData,
            'user_id = :id',
            [':id' => $id]
        );

        if ($updated) {
            logActivity('update', 'users', $id, $oldData, $updateData);
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
     * Reset user password (admin function)
     */
    public function resetPassword($id, $newPassword) {
        // Get user
        $user = $this->getById($id);
        if (!$user) {
            return [
                'success' => false,
                'message' => ERR_NOT_FOUND
            ];
        }

        // Hash new password
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update password
        $updated = $this->db->update('users',
            ['password_hash' => $passwordHash],
            'user_id = :id',
            [':id' => $id]
        );

        if ($updated) {
            logActivity('password_reset', 'users', $id, null, ['reset_by_admin' => true]);
            return [
                'success' => true,
                'message' => 'Password reset successfully.'
            ];
        }

        return [
            'success' => false,
            'message' => ERR_DATABASE_ERROR
        ];
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus($id) {
        // Get current status
        $user = $this->getById($id);
        if (!$user) {
            return [
                'success' => false,
                'message' => ERR_NOT_FOUND
            ];
        }

        $newStatus = $user['is_active'] ? 0 : 1;

        $updated = $this->db->update('users',
            ['is_active' => $newStatus],
            'user_id = :id',
            [':id' => $id]
        );

        if ($updated) {
            logActivity('status_change', 'users', $id, ['is_active' => $user['is_active']], ['is_active' => $newStatus]);
            return [
                'success' => true,
                'message' => $newStatus ? 'User activated successfully.' : 'User deactivated successfully.',
                'is_active' => $newStatus
            ];
        }

        return [
            'success' => false,
            'message' => ERR_DATABASE_ERROR
        ];
    }

    /**
     * Delete user
     */
    public function delete($id) {
        // Don't allow deleting yourself
        if ($id == getCurrentUserId()) {
            return [
                'success' => false,
                'message' => 'You cannot delete your own account.'
            ];
        }

        // Get user data for audit
        $userData = $this->getById($id);

        $deleted = $this->db->delete('users', 'user_id = :id', [':id' => $id]);

        if ($deleted) {
            logActivity('delete', 'users', $id, $userData);
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
     * Get user statistics
     */
    public function getStats() {
        $this->db->query("SELECT
                         COUNT(*) as total,
                         SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins,
                         SUM(CASE WHEN role = 'teacher' THEN 1 ELSE 0 END) as teachers,
                         SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active
                         FROM users");

        return $this->db->fetch();
    }
}
