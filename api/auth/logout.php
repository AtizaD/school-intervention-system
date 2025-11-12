<?php
/**
 * Logout API Endpoint
 */

require_once dirname(__DIR__, 2) . '/config/config.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed', null, 405);
}

// Logout user
$auth = new Auth();
$result = $auth->logout();

// Return response
jsonResponse($result['success'], $result['message']);
