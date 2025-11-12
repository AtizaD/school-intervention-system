<?php
/**
 * List Fees API Endpoint
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Method not allowed', null, 405);
}

// Get filters from query parameters
$studentId = isset($_GET['student_id']) ? (int)$_GET['student_id'] : null;
$class = isset($_GET['class']) ? sanitize($_GET['class']) : null;
$status = isset($_GET['status']) ? sanitize($_GET['status']) : null;
$term = isset($_GET['term']) ? sanitize($_GET['term']) : null;

// Get fees
$feeModel = new Fee();

if ($studentId) {
    $fees = $feeModel->getByStudent($studentId);
} elseif ($status) {
    $fees = $feeModel->getByStatus($status);
} else {
    $fees = $feeModel->getAll();
}

// Filter by class if specified
if ($class && !$studentId) {
    $fees = array_filter($fees, function($fee) use ($class) {
        return $fee['class'] === $class;
    });
    $fees = array_values($fees);
}

// Filter by term if specified
if ($term && !$studentId) {
    $fees = array_filter($fees, function($fee) use ($term) {
        return $fee['term'] === $term;
    });
    $fees = array_values($fees);
}

// Return response
jsonResponse(true, 'Fees retrieved successfully', $fees);
