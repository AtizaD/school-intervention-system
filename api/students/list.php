<?php
/**
 * List Students API Endpoint
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Method not allowed', null, 405);
}

// Get filters from query parameters
$class = isset($_GET['class']) ? sanitize($_GET['class']) : null;
$gender = isset($_GET['gender']) ? sanitize($_GET['gender']) : null;
$status = isset($_GET['status']) ? sanitize($_GET['status']) : 'active';

// Get students
$studentModel = new Student();

if ($class || $gender || $status) {
    $students = $studentModel->search([
        'class' => $class,
        'gender' => $gender,
        'is_active' => $status === 'active' ? 1 : 0
    ]);
} else {
    $students = $studentModel->getAll();
}

// Return response
jsonResponse(true, 'Students retrieved successfully', $students);
