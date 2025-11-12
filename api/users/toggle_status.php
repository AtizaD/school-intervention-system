<?php
/**
 * Toggle User Status API Endpoint
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

// Only admins can toggle user status
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
if (!isset($input['user_id'])) {
    jsonResponse(false, 'User ID is required', null, 400);
}

$userId = (int)$input['user_id'];

// Prevent toggling own status
if ($userId === getCurrentUserId()) {
    jsonResponse(false, 'Cannot change your own status', null, 400);
}

// Toggle status
$userModel = new User();
$result = $userModel->toggleStatus($userId);

// Log activity
if ($result['success']) {
    $action = $result['is_active'] ? 'activated' : 'deactivated';
    logActivity('UPDATE', 'users', $userId, "User {$action} by admin");
}

// Return response
jsonResponse($result['success'], $result['message']);
