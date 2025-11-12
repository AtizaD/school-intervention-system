<?php
/**
 * List Parents API Endpoint
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

// Only admins can access parent data
if (!isAdmin()) {
    jsonResponse(false, 'Unauthorized. Only administrators can access parent data.', null, 403);
}

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Method not allowed', null, 405);
}

// Get parents
$parentModel = new ParentModel();
$parents = $parentModel->getAll();

// Return response
jsonResponse(true, 'Parents retrieved successfully', $parents);
