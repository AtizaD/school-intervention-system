<?php
/**
 * Update System Settings API Endpoint
 */

require_once dirname(__DIR__, 2) . '/middleware/auth.php';

// Only admins can update settings
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
if (!isset($input['settings']) || !is_array($input['settings'])) {
    jsonResponse(false, 'Settings array is required', null, 400);
}

$settings = $input['settings'];

if (empty($settings)) {
    jsonResponse(false, 'No settings to update', null, 400);
}

try {
    $db = new Database();
    $updatedCount = 0;

    foreach ($settings as $key => $value) {
        // Sanitize key and value
        $key = sanitize($key);
        $value = sanitize($value);

        // Update setting
        $db->query("UPDATE settings SET setting_value = :value, updated_at = NOW()
                   WHERE setting_key = :key");
        $db->bind(':value', $value);
        $db->bind(':key', $key);

        if ($db->execute()) {
            $updatedCount++;

            // Log activity for each setting change
            logActivity('UPDATE', 'settings', $key, "Setting {$key} updated to {$value}");
        }
    }

    if ($updatedCount > 0) {
        jsonResponse(true, "{$updatedCount} setting(s) updated successfully");
    } else {
        jsonResponse(false, 'No settings were updated', null, 400);
    }

} catch (Exception $e) {
    error_log("Settings update error: " . $e->getMessage());
    jsonResponse(false, 'An error occurred while updating settings', null, 500);
}
