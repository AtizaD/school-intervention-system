<?php
/**
 * Overdue Fees API Endpoint
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Method not allowed', null, 405);
}

// Get filters
$class = isset($_GET['class']) ? sanitize($_GET['class']) : null;

// Get overdue fees
$feeModel = new Fee();
$overdueFees = $feeModel->getOverdue($class);

// Return response
jsonResponse(true, 'Overdue fees retrieved successfully', $overdueFees);
