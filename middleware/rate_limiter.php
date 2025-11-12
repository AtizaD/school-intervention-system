<?php
/**
 * Rate Limiter Middleware
 * Prevents abuse by limiting request frequency
 */

class RateLimiter {
    private $db;
    private $maxAttempts;
    private $timeWindow; // in seconds

    public function __construct($maxAttempts = 60, $timeWindow = 60) {
        $this->db = new Database();
        $this->maxAttempts = $maxAttempts;
        $this->timeWindow = $timeWindow;
    }

    /**
     * Check if rate limit is exceeded
     */
    public function check($identifier, $action = 'api') {
        $this->cleanup($identifier, $action);

        // Count attempts in time window
        $this->db->query("SELECT COUNT(*) as count
                         FROM rate_limits
                         WHERE identifier = :identifier
                         AND action = :action
                         AND attempted_at > DATE_SUB(NOW(), INTERVAL :window SECOND)");
        $this->db->bind(':identifier', $identifier);
        $this->db->bind(':action', $action);
        $this->db->bind(':window', $this->timeWindow);

        $result = $this->db->fetch();
        $count = $result['count'] ?? 0;

        if ($count >= $this->maxAttempts) {
            return [
                'allowed' => false,
                'attempts' => $count,
                'max_attempts' => $this->maxAttempts,
                'retry_after' => $this->getRetryAfter($identifier, $action)
            ];
        }

        // Log this attempt
        $this->logAttempt($identifier, $action);

        return [
            'allowed' => true,
            'attempts' => $count + 1,
            'max_attempts' => $this->maxAttempts,
            'remaining' => $this->maxAttempts - ($count + 1)
        ];
    }

    /**
     * Log rate limit attempt
     */
    private function logAttempt($identifier, $action) {
        $this->db->insert('rate_limits', [
            'identifier' => $identifier,
            'action' => $action
        ]);
    }

    /**
     * Clean up old attempts
     */
    private function cleanup($identifier, $action) {
        $this->db->query("DELETE FROM rate_limits
                         WHERE identifier = :identifier
                         AND action = :action
                         AND attempted_at < DATE_SUB(NOW(), INTERVAL :window SECOND)");
        $this->db->bind(':identifier', $identifier);
        $this->db->bind(':action', $action);
        $this->db->bind(':window', $this->timeWindow);
        $this->db->execute();
    }

    /**
     * Get retry after timestamp
     */
    private function getRetryAfter($identifier, $action) {
        $this->db->query("SELECT attempted_at
                         FROM rate_limits
                         WHERE identifier = :identifier
                         AND action = :action
                         ORDER BY attempted_at ASC
                         LIMIT 1");
        $this->db->bind(':identifier', $identifier);
        $this->db->bind(':action', $action);

        $result = $this->db->fetch();

        if ($result) {
            $oldestAttempt = strtotime($result['attempted_at']);
            $retryAfter = $oldestAttempt + $this->timeWindow;
            return max(0, $retryAfter - time());
        }

        return 0;
    }

    /**
     * Reset rate limit for identifier
     */
    public function reset($identifier, $action = null) {
        if ($action) {
            $this->db->delete('rate_limits',
                'identifier = :identifier AND action = :action',
                [':identifier' => $identifier, ':action' => $action]
            );
        } else {
            $this->db->delete('rate_limits',
                'identifier = :identifier',
                [':identifier' => $identifier]
            );
        }
    }
}

/**
 * Quick rate limit check function
 */
function checkRateLimit($identifier = null, $action = 'api', $maxAttempts = null, $timeWindow = null) {
    if (!$identifier) {
        $identifier = $_SERVER['REMOTE_ADDR'];
    }

    // Use database settings with fallback to hardcoded constants
    if ($maxAttempts === null) {
        $maxAttempts = defined('DB_RATE_LIMIT_REQUESTS') ? DB_RATE_LIMIT_REQUESTS : (defined('RATE_LIMIT_REQUESTS') ? RATE_LIMIT_REQUESTS : 100);
    }
    if ($timeWindow === null) {
        $timeWindow = defined('RATE_LIMIT_WINDOW') ? RATE_LIMIT_WINDOW : 3600;
    }

    $limiter = new RateLimiter($maxAttempts, $timeWindow);
    $result = $limiter->check($identifier, $action);

    if (!$result['allowed']) {
        http_response_code(429);
        header('Retry-After: ' . $result['retry_after']);

        if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $result['retry_after']
            ]);
        } else {
            echo "Too many requests. Please try again in " . $result['retry_after'] . " seconds.";
        }

        exit;
    }

    return $result;
}
