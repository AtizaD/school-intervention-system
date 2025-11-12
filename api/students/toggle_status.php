<?php
/**
 * Toggle Student Status API Endpoint
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed', null, 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['student_id'])) {
    jsonResponse(false, 'Student ID is required');
}

// Toggle status
$studentModel = new Student();
$result = $studentModel->toggleStatus($input['student_id']);

// Return response
jsonResponse($result['success'], $result['message']);
