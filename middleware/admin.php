<?php
/**
 * Admin Middleware
 * Ensures only admin users can access protected resources
 */

// Load auth middleware first
require_once __DIR__ . '/auth.php';

// Check if user is admin
if (!isAdmin()) {
    // If it's an API request, return JSON
    if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => ERR_ACCESS_DENIED
        ]);
        exit;
    }

    // Otherwise redirect to dashboard
    header('Location: ' . APP_URL . '/pages/dashboard.php');
    exit;
}
