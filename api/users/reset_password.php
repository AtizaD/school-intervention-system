<?php
/**
 * Reset User Password API Endpoint
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

// Only admins can reset passwords
if (!isAdmin()) {
    jsonResponse(false, ERR_ACCESS_DENIED, null, 403);
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed', null, 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['user_id']) || !isset($input['new_password'])) {
    jsonResponse(false, 'User ID and new password are required', null, 400);
}

$userId = (int)$input['user_id'];
$newPassword = trim($input['new_password']);

// Prevent resetting own password through this endpoint
if ($userId === getCurrentUserId()) {
    jsonResponse(false, 'Cannot reset your own password through this method. Use the profile page instead.', null, 400);
}

// Validate password strength
if (strlen($newPassword) < 8) {
    jsonResponse(false, 'Password must be at least 8 characters long', null, 400);
}

if (!preg_match('/[A-Z]/', $newPassword)) {
    jsonResponse(false, 'Password must contain at least one uppercase letter', null, 400);
}

if (!preg_match('/[a-z]/', $newPassword)) {
    jsonResponse(false, 'Password must contain at least one lowercase letter', null, 400);
}

if (!preg_match('/[0-9]/', $newPassword)) {
    jsonResponse(false, 'Password must contain at least one number', null, 400);
}

if (!preg_match('/[^A-Za-z0-9]/', $newPassword)) {
    jsonResponse(false, 'Password must contain at least one special character', null, 400);
}

// Reset password
$userModel = new User();
$result = $userModel->resetPassword($userId, $newPassword);

// Log activity
if ($result['success']) {
    logActivity('UPDATE', 'users', $userId, 'Password reset by admin');
}

// Return response
jsonResponse($result['success'], $result['message']);
