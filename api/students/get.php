<?php
/**
 * Get Student API Endpoint
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Method not allowed', null, 405);
}

$studentId = $_GET['id'] ?? '';

if (empty($studentId)) {
    jsonResponse(false, 'Student ID is required');
}

// Get student
$studentModel = new Student();
$student = $studentModel->getWithParents($studentId);

if ($student) {
    jsonResponse(true, 'Student found', $student);
} else {
    jsonResponse(false, 'Student not found', null, 404);
}
