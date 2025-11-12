<?php
/**
 * Get All Programs
 * Returns list of all available programs
 */

// Security check
if (!defined('APP_ACCESS')) {
    define('APP_ACCESS', true);
}

require_once dirname(__DIR__, 2) . '/config/config.php';

// Set response header
header('Content-Type: application/json');

try {
    // Get database connection
    $db = new Database();

    // Query to get all programs
    $db->query("SELECT program_id, program_name
                FROM programs
                ORDER BY program_name ASC");

    $programs = $db->fetchAll();

    // Return success response
    echo json_encode([
        'success' => true,
        'programs' => $programs
    ]);

} catch (Exception $e) {
    // Log error
    error_log("API Error (get_programs.php): " . $e->getMessage());

    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch programs'
    ]);
}
