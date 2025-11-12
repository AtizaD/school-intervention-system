<?php
/**
 * Main Configuration File
 * Intervention - School Money Collection System
 */

// Prevent multiple loads
if (defined('CONFIG_LOADED')) {
    return;
}
define('CONFIG_LOADED', true);

// Prevent direct access
define('APP_ACCESS', true);

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set timezone
date_default_timezone_set('Africa/Accra');

// Application Settings
define('APP_NAME', 'INTERVENTION');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'https://intervention.bassflix.xyz');
define('APP_ENV', 'development'); // development, production

// Directory paths
define('BASE_PATH', dirname(__DIR__));
define('CONFIG_PATH', BASE_PATH . '/config');
define('CLASSES_PATH', BASE_PATH . '/classes');
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('MIDDLEWARE_PATH', BASE_PATH . '/middleware');
define('UPLOADS_PATH', BASE_PATH . '/uploads');
define('LOGS_PATH', BASE_PATH . '/logs');

// URL paths
define('ASSETS_URL', APP_URL . '/assets');
define('CSS_URL', ASSETS_URL . '/css');
define('JS_URL', ASSETS_URL . '/js');
define('IMG_URL', ASSETS_URL . '/images');

// Security settings
define('SESSION_LIFETIME', 7200); // 2 hours in seconds
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_ATTEMPT_WINDOW', 900); // 15 minutes in seconds
define('RATE_LIMIT_REQUESTS', 100);
define('RATE_LIMIT_WINDOW', 3600); // 1 hour in seconds

// Password requirements
define('MIN_PASSWORD_LENGTH', 8);
define('PASSWORD_REQUIRE_UPPERCASE', true);
define('PASSWORD_REQUIRE_LOWERCASE', true);
define('PASSWORD_REQUIRE_NUMBER', true);
define('PASSWORD_REQUIRE_SPECIAL', true);

// Upload settings
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf']);

// Pagination settings
define('RECORDS_PER_PAGE', 20);

// Date format
define('DATE_FORMAT', 'd/m/Y');
define('DATETIME_FORMAT', 'd/m/Y H:i:s');

// SMS Settings (Hubtel)
define('SMS_ENABLED', false);
define('SMS_API_KEY', ''); // Add your Hubtel API key
define('SMS_API_SECRET', ''); // Add your Hubtel API secret
define('SMS_SENDER_ID', 'INTERVENTION');

// Email Settings (for future use)
define('MAIL_ENABLED', false);
define('MAIL_FROM', 'noreply@intervention.bassflix.xyz');
define('MAIL_FROM_NAME', 'Intervention System');

// Load database configuration
require_once CONFIG_PATH . '/database.php';

// Load constants
require_once CONFIG_PATH . '/constants.php';

// Load helper functions
require_once INCLUDES_PATH . '/functions.php';

// Auto-load classes
spl_autoload_register(function ($class) {
    $file = CLASSES_PATH . '/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Load dynamic settings from database and override hardcoded constants
try {
    $db = getDatabaseConnection();

    // Get security settings from database
    $maxLoginAttempts = getSetting('max_login_attempts', MAX_LOGIN_ATTEMPTS);
    $rateLimitRequests = getSetting('rate_limit_requests', RATE_LIMIT_REQUESTS);
    $sessionTimeoutMinutes = getSetting('session_timeout_minutes', 120);

    // Redefine constants with database values (using runkit if available, otherwise use globals)
    if (!defined('DB_MAX_LOGIN_ATTEMPTS')) {
        define('DB_MAX_LOGIN_ATTEMPTS', (int)$maxLoginAttempts);
        define('DB_RATE_LIMIT_REQUESTS', (int)$rateLimitRequests);
        define('DB_SESSION_LIFETIME', (int)$sessionTimeoutMinutes * 60); // Convert to seconds
    }
} catch (Exception $e) {
    error_log("Failed to load dynamic settings: " . $e->getMessage());
    // Fallback to hardcoded constants if database fails
    if (!defined('DB_MAX_LOGIN_ATTEMPTS')) {
        define('DB_MAX_LOGIN_ATTEMPTS', MAX_LOGIN_ATTEMPTS);
        define('DB_RATE_LIMIT_REQUESTS', RATE_LIMIT_REQUESTS);
        define('DB_SESSION_LIFETIME', SESSION_LIFETIME);
    }
}

// Helper function to sanitize input
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Helper function to format date
function formatDate($date, $format = DATE_FORMAT) {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

// Helper function to format currency
function formatCurrency($amount) {
    return CURRENCY . ' ' . number_format($amount, 2);
}

// Helper function to redirect
function redirect($url) {
    header("Location: $url");
    exit;
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Helper function to check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Helper function to get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Helper function to get current user role
function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

// Helper function to log activity
function logActivity($action, $table_name, $record_id = null, $old_values = null, $new_values = null) {
    try {
        $db = getDatabaseConnection();
        $sql = "INSERT INTO audit_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            getCurrentUserId(),
            $action,
            $table_name,
            $record_id,
            $old_values ? json_encode($old_values) : null,
            $new_values ? json_encode($new_values) : null,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    } catch (Exception $e) {
        error_log("Audit Log Error: " . $e->getMessage());
    }
}

// Helper function to generate JSON response
function jsonResponse($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}
