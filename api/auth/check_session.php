<?php
/**
 * Check Session API Endpoint
 */

require_once dirname(__DIR__, 2) . '/config/config.php';

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Method not allowed', null, 405);
}

// Check if user is logged in
$auth = new Auth();
$isAuthenticated = $auth->check();

if ($isAuthenticated) {
    jsonResponse(true, 'Session valid', [
        'user_id' => $_SESSION['user_id'],
        'fullname' => $_SESSION['fullname'],
        'role' => $_SESSION['role']
    ]);
} else {
    jsonResponse(false, 'Session expired', null, 401);
}
