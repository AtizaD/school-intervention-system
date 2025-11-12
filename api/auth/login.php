<?php
/**
 * Login API Endpoint
 */

require_once dirname(__DIR__, 2) . '/config/config.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed', null, 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Get contact and password
$contact = $input['contact'] ?? $_POST['contact'] ?? '';
$password = $input['password'] ?? $_POST['password'] ?? '';

// Validate input
if (empty($contact) || empty($password)) {
    jsonResponse(false, 'Contact number and password are required.');
}

// Attempt login
$auth = new Auth();
$result = $auth->login($contact, $password);

// Return response
if ($result['success']) {
    jsonResponse(true, $result['message'], $result['user']);
} else {
    jsonResponse(false, $result['message'], null, 401);
}
