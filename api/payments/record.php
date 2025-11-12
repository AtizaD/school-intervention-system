<?php
/**
 * Record Payment API Endpoint
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed', null, 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Record payment
$paymentModel = new Payment();
$result = $paymentModel->record($input);

// Return response with receipt number
if ($result['success']) {
    jsonResponse(true, $result['message'], [
        'payment_id' => $result['payment_id'],
        'receipt_number' => $result['receipt_number']
    ]);
} else {
    jsonResponse(false, $result['message']);
}
