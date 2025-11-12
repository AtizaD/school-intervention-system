<?php
/**
 * Create Student API Endpoint
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

// Only admins can create students
if (!isAdmin()) {
    jsonResponse(false, 'Unauthorized. Only administrators can add students.', null, 403);
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

    // Create student
    $studentModel = new Student();
    $studentResult = $studentModel->create($studentData);

    if (!$studentResult['success']) {
        $db->rollBack();
        jsonResponse(false, $studentResult['message'], null);
    }

    $studentId = $studentResult['student_id'];

    // Check if parent already exists by contact
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

    // Link student to parent
    $db->query("INSERT INTO student_parents (student_id, parent_id, is_primary)
               VALUES (:student_id, :parent_id, 1)");
    $db->bind(':student_id', $studentId);
    $db->bind(':parent_id', $parentId);
    $db->execute();

    $db->commit();

    // Return response
    jsonResponse(true, 'Student and parent information saved successfully', [
        'student_id' => $studentId,
        'parent_id' => $parentId
    ]);

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Create student error: " . $e->getMessage());
    jsonResponse(false, 'An error occurred while creating the student', null, 500);
}
