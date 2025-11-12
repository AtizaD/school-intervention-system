<?php
/**
 * Assign Fee API Endpoint
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

// Assign fee
$feeModel = new Fee();
$result = $feeModel->assign($input);

// Return response
jsonResponse($result['success'], $result['message'], $result['fee_id'] ?? null);
