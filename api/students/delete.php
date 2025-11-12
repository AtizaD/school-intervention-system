<?php
/**
 * Delete Student API Endpoint
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

// Only admins can delete students
if (!isAdmin()) {
    jsonResponse(false, ERR_ACCESS_DENIED, null, 403);
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed', null, 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['student_id'])) {
    jsonResponse(false, 'Student ID is required', null, 400);
}

$studentId = (int)$input['student_id'];

// Delete student
$studentModel = new Student();
$result = $studentModel->delete($studentId);

// Return response
jsonResponse($result['success'], $result['message']);
