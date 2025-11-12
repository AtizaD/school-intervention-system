<?php
/**
 * Get Payments List API Endpoint
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Method not allowed', null, 405);
}

// Get filters
$filters = [];

if (!empty($_GET['student_id'])) {
    $filters['student_id'] = $_GET['student_id'];
}

if (!empty($_GET['payment_method'])) {
    $filters['payment_method'] = $_GET['payment_method'];
}

if (!empty($_GET['date_from'])) {
    $filters['date_from'] = $_GET['date_from'];
}

if (!empty($_GET['date_to'])) {
    $filters['date_to'] = $_GET['date_to'];
}

if (!empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}

// Get payments
$paymentModel = new Payment();
$payments = $paymentModel->getAll($filters);

jsonResponse(true, 'Payments retrieved successfully', $payments);
