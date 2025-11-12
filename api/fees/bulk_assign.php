<?php
/**
 * Bulk Assign Fees API Endpoint
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

// Only admins can assign fees
if (!isAdmin()) {
    jsonResponse(false, 'Unauthorized. Only administrators can assign fees.', null, 403);
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed', null, 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['student_ids']) || !is_array($input['student_ids'])) {
    jsonResponse(false, 'Please select at least one student');
}

if (empty($input['amount']) || empty($input['due_date'])) {
    jsonResponse(false, 'Amount and due date are required');
}

// Bulk assign fees
$feeModel = new Fee();
$result = $feeModel->bulkAssign($input['student_ids'], $input['amount'], $input['due_date']);

// Return response
jsonResponse($result['success'], $result['message'], [
    'success_count' => $result['success_count'] ?? 0,
    'failed_students' => $result['failed_students'] ?? []
]);
