<?php
/**
 * Parent/Guardian Class
 * Handles parent/guardian management operations
 */

class ParentModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Get all parents
     */
    public function getAll() {
        $this->db->query("SELECT p.*,
                         COUNT(DISTINCT sp.student_id) as student_count
                         FROM parents p
                         LEFT JOIN student_parents sp ON p.parent_id = sp.parent_id
                         GROUP BY p.parent_id
                         ORDER BY p.fullname ASC");

        return $this->db->fetchAll();
    }

    /**
     * Get parent by ID
     */
    public function getById($id) {
        $this->db->query("SELECT * FROM parents WHERE parent_id = :id");
        $this->db->bind(':id', $id);
        return $this->db->fetch();
    }

    /**
     * Get parent by contact
     */
    public function getByContact($contact) {
        $this->db->query("SELECT * FROM parents WHERE contact = :contact");
        $this->db->bind(':contact', $contact);
        return $this->db->fetch();
    }

    /**
     * Get parent's students
     */
    public function getStudents($parentId) {
        $this->db->query("SELECT s.*
                         FROM students s
                         INNER JOIN student_parents sp ON s.student_id = sp.student_id
                         WHERE sp.parent_id = :parent_id
                         ORDER BY s.first_name, s.last_name");
        $this->db->bind(':parent_id', $parentId);
        return $this->db->fetchAll();
    }

    /**
     * Create new parent
     */
    public function create($data) {
        // Validate input
        $validator = new Validator();
        $validator->required('fullname', $data['fullname'])
                  ->minLength('fullname', $data['fullname'], 3)
                  ->required('relationship', $data['relationship'])
                  ->inArray('relationship', $data['relationship'], ['father', 'mother', 'guardian', 'other'])
                  ->required('contact', $data['contact'])
                  ->phone('contact', $data['contact'])
                  ->unique('contact', $data['contact'], 'parents', 'contact');

        if (!$validator->isValid()) {
            return [
                'success' => false,
                'message' => $validator->getFirstError(),
                'errors' => $validator->getErrors()
            ];
        }

        // Insert parent
        $parentId = $this->db->insert('parents', [
            'fullname' => sanitize($data['fullname']),
            'relationship' => $data['relationship'],
            'contact' => sanitize($data['contact']),
            'email' => isset($data['email']) ? sanitize($data['email']) : null,
            'address' => isset($data['address']) ? sanitize($data['address']) : null,
            'occupation' => isset($data['occupation']) ? sanitize($data['occupation']) : null
        ]);

        if ($parentId) {
            logActivity('CREATE', 'parents', $parentId);
            return [
                'success' => true,
                'message' => MSG_CREATE_SUCCESS,
                'parent_id' => $parentId
            ];
        }

        return [
            'success' => false,
            'message' => ERR_DATABASE_ERROR
        ];
    }

    /**
     * Update parent
     */
    public function update($id, $data) {
        // Validate input
        $validator = new Validator();
        $validator->required('fullname', $data['fullname'])
                  ->minLength('fullname', $data['fullname'], 3)
                  ->required('relationship', $data['relationship'])
                  ->inArray('relationship', $data['relationship'], ['father', 'mother', 'guardian', 'other'])
                  ->required('contact', $data['contact'])
                  ->phone('contact', $data['contact']);

        if (!$validator->isValid()) {
            return [
                'success' => false,
                'message' => $validator->getFirstError(),
                'errors' => $validator->getErrors()
            ];
        }

        // Check if contact is unique (excluding current parent)
        $existingParent = $this->getByContact($data['contact']);
        if ($existingParent && $existingParent['parent_id'] != $id) {
            return [
                'success' => false,
                'message' => 'Contact number already in use.'
            ];
        }

        // Get old values for audit
        $oldData = $this->getById($id);

        // Update parent
        $updated = $this->db->update('parents', [
            'fullname' => sanitize($data['fullname']),
            'relationship' => $data['relationship'],
            'contact' => sanitize($data['contact']),
            'email' => isset($data['email']) ? sanitize($data['email']) : null,
            'address' => isset($data['address']) ? sanitize($data['address']) : null,
            'occupation' => isset($data['occupation']) ? sanitize($data['occupation']) : null
        ], 'parent_id = :id', [':id' => $id]);

        if ($updated) {
            logActivity('UPDATE', 'parents', $id);
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
     * Delete parent
     */
    public function delete($id) {
        // Get parent data for audit
        $parentData = $this->getById($id);

        if (!$parentData) {
            return [
                'success' => false,
                'message' => ERR_NOT_FOUND
            ];
        }

        // Delete student associations first
        $this->db->delete('student_parents', 'parent_id = :id', [':id' => $id]);

        // Delete parent
        $deleted = $this->db->delete('parents', 'parent_id = :id', [':id' => $id]);

        if ($deleted) {
            logActivity('DELETE', 'parents', $id);
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
     * Link student to parent
     */
    public function linkStudent($parentId, $studentId) {
        // Check if link already exists
        $this->db->query("SELECT * FROM student_parents
                         WHERE parent_id = :parent_id AND student_id = :student_id");
        $this->db->bind(':parent_id', $parentId);
        $this->db->bind(':student_id', $studentId);

        if ($this->db->fetch()) {
            return [
                'success' => false,
                'message' => 'Student is already linked to this parent.'
            ];
        }

        // Create link
        $linkId = $this->db->insert('student_parents', [
            'student_id' => $studentId,
            'parent_id' => $parentId
        ]);

        if ($linkId) {
            logActivity('CREATE', 'student_parents', $linkId);
            return [
                'success' => true,
                'message' => 'Student linked to parent successfully.'
            ];
        }

        return [
            'success' => false,
            'message' => ERR_DATABASE_ERROR
        ];
    }

    /**
     * Unlink student from parent
     */
    public function unlinkStudent($parentId, $studentId) {
        $deleted = $this->db->delete('student_parents',
            'parent_id = :parent_id AND student_id = :student_id',
            [':parent_id' => $parentId, ':student_id' => $studentId]
        );

        if ($deleted) {
            logActivity('DELETE', 'student_parents', null);
            return [
                'success' => true,
                'message' => 'Student unlinked from parent successfully.'
            ];
        }

        return [
            'success' => false,
            'message' => ERR_DATABASE_ERROR
        ];
    }

    /**
     * Get parent statistics
     */
    public function getStats() {
        $this->db->query("SELECT
                         COUNT(*) as total,
                         SUM(CASE WHEN relationship = 'father' THEN 1 ELSE 0 END) as fathers,
                         SUM(CASE WHEN relationship = 'mother' THEN 1 ELSE 0 END) as mothers,
                         SUM(CASE WHEN relationship = 'guardian' THEN 1 ELSE 0 END) as guardians
                         FROM parents");

        return $this->db->fetch();
    }
}
