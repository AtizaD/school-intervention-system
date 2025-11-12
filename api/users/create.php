<?php
/**
 * Create User API Endpoint
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

// Only admins can create users
if (!isAdmin()) {
    jsonResponse(false, ERR_ACCESS_DENIED, null, 403);
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed', null, 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Create user
$userModel = new User();
$result = $userModel->create($input);

// Return response
jsonResponse($result['success'], $result['message'], $result['user_id'] ?? null);
