<?php
/**
 * Payment Summary API Endpoint
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Method not allowed', null, 405);
}

// Get date range from query parameters
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-t');
$class = isset($_GET['class']) ? sanitize($_GET['class']) : null;
$paymentMethod = isset($_GET['payment_method']) ? sanitize($_GET['payment_method']) : null;

// Get payment model
$paymentModel = new Payment();

// Get payments in date range
$payments = $paymentModel->getByDateRange($dateFrom, $dateTo, $class);

// Filter by payment method if specified
if ($paymentMethod) {
    $payments = array_filter($payments, function($payment) use ($paymentMethod) {
        return $payment['payment_method'] === $paymentMethod;
    });
    $payments = array_values($payments);
}

// Calculate summary
$summary = [
    'total_payments' => count($payments),
    'total_amount' => array_sum(array_column($payments, 'amount_paid')),
    'date_from' => $dateFrom,
    'date_to' => $dateTo,
    'class' => $class,
    'payment_method' => $paymentMethod
];

// Group by payment method
$byMethod = [];
foreach ($payments as $payment) {
    $method = $payment['payment_method'];
    if (!isset($byMethod[$method])) {
        $byMethod[$method] = [
            'count' => 0,
            'total' => 0
        ];
    }
    $byMethod[$method]['count']++;
    $byMethod[$method]['total'] += $payment['amount_paid'];
}

$summary['by_payment_method'] = $byMethod;

// Group by date
$byDate = [];
foreach ($payments as $payment) {
    $date = date('Y-m-d', strtotime($payment['payment_date']));
    if (!isset($byDate[$date])) {
        $byDate[$date] = [
            'count' => 0,
            'total' => 0
        ];
    }
    $byDate[$date]['count']++;
    $byDate[$date]['total'] += $payment['amount_paid'];
}

$summary['by_date'] = $byDate;

// Return response
jsonResponse(true, 'Payment summary retrieved successfully', $summary);
