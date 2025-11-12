<?php
/**
 * Authentication Middleware
 * Checks if user is logged in
 */

// Load config
require_once dirname(__DIR__) . '/config/config.php';

// Create Auth instance
$auth = new Auth();

// Check if user is authenticated
if (!$auth->check()) {
    // If AJAX request, return JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        jsonResponse(false, ERR_SESSION_EXPIRED, null, 401);
    }

    // Otherwise redirect to login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/';
    redirect(APP_URL . '/pages/login.php');
}

// Clean up old sessions periodically (1% chance)
if (rand(1, 100) === 1) {
    $auth->cleanupSessions();
}
