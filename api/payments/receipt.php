<?php
/**
 * Generate Payment Receipt API Endpoint
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Method not allowed', null, 405);
}

// Get payment ID
if (!isset($_GET['payment_id'])) {
    jsonResponse(false, 'Payment ID is required', null, 400);
}

$paymentId = (int)$_GET['payment_id'];

// Generate receipt
$pdf = new PDF();
$result = $pdf->generateReceipt($paymentId);

if ($result['success']) {
    // Return file for download
    $filepath = $result['filepath'];

    if (file_exists($filepath)) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $result['filename'] . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }
}

jsonResponse(false, $result['message'] ?? 'Failed to generate receipt', null, 500);
