<?php
/**
 * Update Student API Endpoint
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

// Only admins can update students
if (!isAdmin()) {
    jsonResponse(false, 'Unauthorized. Only administrators can edit students.', null, 403);
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed', null, 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input structure
if (!isset($input['student']) || !isset($input['parent'])) {
    jsonResponse(false, 'Invalid input: student and parent data required', null, 400);
}

$studentData = $input['student'];
$parentData = $input['parent'];

if (empty($studentData['student_id'])) {
    jsonResponse(false, 'Student ID is required');
}

$studentId = $studentData['student_id'];
unset($studentData['student_id']); // Remove from update data

// Validate required fields
$requiredStudentFields = ['first_name', 'last_name', 'gender', 'class', 'house'];
foreach ($requiredStudentFields as $field) {
    if (empty($studentData[$field])) {
        jsonResponse(false, "Student field '$field' is required", null, 400);
    }
}

$requiredParentFields = ['fullname', 'relationship', 'contact'];
foreach ($requiredParentFields as $field) {
    if (empty($parentData[$field])) {
        jsonResponse(false, "Parent field '$field' is required", null, 400);
    }
}

try {
    $db = new Database();
    $db->beginTransaction();

    // Update student
    $studentModel = new Student();
    $result = $studentModel->update($studentId, $studentData);

    if (!$result['success']) {
        $db->rollBack();
        jsonResponse(false, $result['message'], null);
    }

    // Handle parent update/creation
    if (!empty($parentData['parent_id'])) {
        // Update existing parent
        $db->query("UPDATE parents
                   SET fullname = :fullname, relationship = :relationship, contact = :contact
                   WHERE parent_id = :parent_id");
        $db->bind(':parent_id', $parentData['parent_id']);
        $db->bind(':fullname', $parentData['fullname']);
        $db->bind(':relationship', $parentData['relationship']);
        $db->bind(':contact', $parentData['contact']);
        $db->execute();
        $parentId = $parentData['parent_id'];
    } else {
        // Check if parent exists by contact
        $db->query("SELECT parent_id FROM parents WHERE contact = :contact LIMIT 1");
        $db->bind(':contact', $parentData['contact']);
        $existingParent = $db->fetchOne();

        if ($existingParent) {
            // Use existing parent
            $parentId = $existingParent['parent_id'];
        } else {
            // Create new parent
            $db->query("INSERT INTO parents (fullname, relationship, contact)
                       VALUES (:fullname, :relationship, :contact)");
            $db->bind(':fullname', $parentData['fullname']);
            $db->bind(':relationship', $parentData['relationship']);
            $db->bind(':contact', $parentData['contact']);
            $db->execute();
            $parentId = $db->lastInsertId();
        }

        // Link student to parent if not already linked
        $db->query("INSERT IGNORE INTO student_parents (student_id, parent_id, is_primary)
                   VALUES (:student_id, :parent_id, 1)");
        $db->bind(':student_id', $studentId);
        $db->bind(':parent_id', $parentId);
        $db->execute();
    }

    $db->commit();

    // Return response
    jsonResponse(true, 'Student and parent information updated successfully');

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Update student error: " . $e->getMessage());
    jsonResponse(false, 'An error occurred while updating the student', null, 500);
}
