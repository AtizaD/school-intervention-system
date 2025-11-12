<?php
/**
 * Create Parent API Endpoint
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

// Create parent
$parentModel = new ParentModel();
$result = $parentModel->create($input);

// If successful and student_ids provided, link students
if ($result['success'] && isset($input['student_ids']) && is_array($input['student_ids'])) {
    $parentId = $result['parent_id'];
    foreach ($input['student_ids'] as $studentId) {
        $parentModel->linkStudent($parentId, $studentId);
    }
}

// Return response
jsonResponse($result['success'], $result['message'], $result['parent_id'] ?? null);
