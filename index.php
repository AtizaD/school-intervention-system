<?php
/**
 * Entry Point
 * Redirects to appropriate page based on authentication
 */

require_once __DIR__ . '/config/config.php';

// Check if user is logged in
if (isLoggedIn()) {
    // Redirect to dashboard
    redirect(APP_URL . '/pages/dashboard.php');
} else {
    // Redirect to login
    redirect(APP_URL . '/pages/login.php');
}
