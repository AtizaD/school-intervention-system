<?php
/**
 * Update Parent API Endpoint
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

// Only admins can manage parents
if (!isAdmin()) {
    jsonResponse(false, 'Unauthorized. Only administrators can manage parents.', null, 403);
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed', null, 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['parent_id'])) {
    jsonResponse(false, 'Parent ID is required', null, 400);
}

$parentId = (int)$input['parent_id'];

// Update parent
$parentModel = new ParentModel();
$result = $parentModel->update($parentId, $input);

// Return response
jsonResponse($result['success'], $result['message']);
