<?php
/**
 * Auth Class
 * Handles authentication and authorization
 */

class Auth {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Login user
     */
    public function login($contact, $password) {
        // Check rate limiting
        if (!$this->checkLoginAttempts($contact)) {
            return [
                'success' => false,
                'message' => 'Too many login attempts. Please try again in 15 minutes.'
            ];
        }

        // Sanitize input
        $contact = sanitize($contact);

        // Get user from database
        $this->db->query("SELECT * FROM users WHERE contact = :contact AND is_active = 1");
        $this->db->bind(':contact', $contact);
        $user = $this->db->fetch();

        // Log login attempt
        $this->logLoginAttempt($contact, $user ? true : false);

        if (!$user) {
            return [
                'success' => false,
                'message' => ERR_LOGIN_FAILED
            ];
        }

        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            return [
                'success' => false,
                'message' => ERR_LOGIN_FAILED
            ];
        }

        // Create session
        $this->createSession($user);

        // Update last login
        $this->db->update('users',
            ['last_login' => date('Y-m-d H:i:s')],
            'user_id = :id',
            [':id' => $user['user_id']]
        );

        // Log activity
        logActivity('login', 'users', $user['user_id']);

        return [
            'success' => true,
            'message' => MSG_LOGIN_SUCCESS,
            'user' => [
                'user_id' => $user['user_id'],
                'fullname' => $user['fullname'],
                'contact' => $user['contact'],
                'role' => $user['role']
            ]
        ];
    }

    /**
     * Logout user
     */
    public function logout() {
        // Delete session from database
        if (isset($_SESSION['session_id'])) {
            $this->db->delete('sessions', 'session_id = :id', [
                ':id' => $_SESSION['session_id']
            ]);
        }

        // Log activity
        if (isset($_SESSION['user_id'])) {
            logActivity('logout', 'users', $_SESSION['user_id']);
        }

        // Destroy session
        session_unset();
        session_destroy();

        return [
            'success' => true,
            'message' => MSG_LOGOUT_SUCCESS
        ];
    }

    /**
     * Check if user is authenticated
     */
    public function check() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_id'])) {
            return false;
        }

        // Verify session in database
        $this->db->query("SELECT * FROM sessions WHERE session_id = :id AND expires_at > NOW()");
        $this->db->bind(':id', $_SESSION['session_id']);
        $session = $this->db->fetch();

        if (!$session) {
            $this->logout();
            return false;
        }

        // Update last activity
        $this->db->update('sessions',
            ['last_activity' => date('Y-m-d H:i:s')],
            'session_id = :id',
            [':id' => $_SESSION['session_id']]
        );

        return true;
    }

    /**
     * Create user session
     */
    private function createSession($user) {
        $sessionId = bin2hex(random_bytes(32));
        $sessionLifetime = defined('DB_SESSION_LIFETIME') ? DB_SESSION_LIFETIME : SESSION_LIFETIME;
        $expiresAt = date('Y-m-d H:i:s', time() + $sessionLifetime);

        // Store in database
        $this->db->insert('sessions', [
            'session_id' => $sessionId,
            'user_id' => $user['user_id'],
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'expires_at' => $expiresAt
        ]);

        // Store in PHP session
        $_SESSION['session_id'] = $sessionId;
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['contact'] = $user['contact'];
        $_SESSION['role'] = $user['role'];
    }

    /**
     * Check login attempts for rate limiting
     */
    private function checkLoginAttempts($contact) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';

        // Clean up old attempts
        $this->db->delete('login_attempts',
            'attempt_time < DATE_SUB(NOW(), INTERVAL 15 MINUTE)',
            []
        );

        // Count recent failed attempts
        $this->db->query("SELECT COUNT(*) as count FROM login_attempts
                         WHERE ip_address = :ip
                         AND success = 0
                         AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
        $this->db->bind(':ip', $ip);
        $result = $this->db->fetch();

        $maxAttempts = defined('DB_MAX_LOGIN_ATTEMPTS') ? DB_MAX_LOGIN_ATTEMPTS : MAX_LOGIN_ATTEMPTS;
        return $result['count'] < $maxAttempts;
    }

    /**
     * Log login attempt
     */
    private function logLoginAttempt($contact, $success) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';

        $this->db->insert('login_attempts', [
            'ip_address' => $ip,
            'contact' => $contact,
            'success' => $success ? 1 : 0
        ]);
    }

    /**
     * Change password
     */
    public function changePassword($userId, $oldPassword, $newPassword) {
        // Get user
        $this->db->query("SELECT password_hash FROM users WHERE user_id = :id");
        $this->db->bind(':id', $userId);
        $user = $this->db->fetch();

        if (!$user) {
            return ['success' => false, 'message' => 'User not found.'];
        }

        // Verify old password
        if (!password_verify($oldPassword, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Current password is incorrect.'];
        }

        // Hash new password
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update password
        $updated = $this->db->update('users',
            ['password_hash' => $newHash],
            'user_id = :id',
            [':id' => $userId]
        );

        if ($updated) {
            logActivity('password_change', 'users', $userId);
            return ['success' => true, 'message' => 'Password changed successfully.'];
        }

        return ['success' => false, 'message' => 'Failed to change password.'];
    }

    /**
     * Clean up expired sessions
     */
    public function cleanupSessions() {
        $this->db->delete('sessions', 'expires_at < NOW()', []);
    }
}
