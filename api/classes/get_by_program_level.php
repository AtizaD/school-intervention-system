<?php
/**
 * Get Classes by Program and Level
 * Returns classes filtered by program_id and level
 */

// Security check
if (!defined('APP_ACCESS')) {
    define('APP_ACCESS', true);
}

require_once dirname(__DIR__, 2) . '/config/config.php';

// Set response header
header('Content-Type: application/json');

try {
    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST requests are allowed');
    }

    // Get and decode POST data
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate program_id
    if (!isset($input['program_id'])) {
        throw new Exception('Program ID is required');
    }

    $programId = filter_var($input['program_id'], FILTER_VALIDATE_INT);
    if ($programId === false || $programId <= 0) {
        throw new Exception('Invalid program ID');
    }

    // Validate level
    if (!isset($input['level'])) {
        throw new Exception('Level is required');
    }

    $level = trim($input['level']);
    if (empty($level)) {
        throw new Exception('Invalid level');
    }

    // Get database connection
    $db = new Database();

    // Query to get classes for the specified program and level
    // Ordered by numeric portion of class_name for natural sorting (1, 2, 10 instead of 1, 10, 2)
    $db->query("SELECT class_id, class_name
                FROM classes
                WHERE program_id = :program_id
                AND level = :level
                ORDER BY
                    CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(class_name, ' ', -1), ' ', 1) AS UNSIGNED),
                    class_name");

    $db->bind(':program_id', $programId);
    $db->bind(':level', $level);

    $classes = $db->fetchAll();

    // Return success response
    echo json_encode([
        'success' => true,
        'classes' => $classes
    ]);

} catch (Exception $e) {
    // Log error
    error_log("API Error (get_by_program_level.php): " . $e->getMessage());

    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
