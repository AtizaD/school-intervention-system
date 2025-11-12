# Security Settings Integration

**Date:** 2025-11-08
**Status:** ✅ Fully Implemented and Tested

---

## Overview

The system now loads security settings dynamically from the database instead of using hardcoded constants. This allows administrators to adjust security parameters through the Settings page without modifying code.

---

## Security Settings

All security settings are stored in the `settings` table with `category = 'security'`:

| Setting Key | Description | Default Value | Unit |
|-------------|-------------|---------------|------|
| `max_login_attempts` | Maximum login attempts before lockout | 5 | attempts |
| `rate_limit_requests` | Maximum requests per IP per hour | 100 | requests |
| `session_timeout_minutes` | Session timeout duration | 120 | minutes |

---

## How It Works

### 1. Database Settings Storage

Settings are stored in the `settings` table:

```sql
SELECT setting_key, setting_value, description
FROM settings
WHERE category = 'security';
```

Result:
```
+-------------------------+---------------+----------------------------------------+
| setting_key             | setting_value | description                            |
+-------------------------+---------------+----------------------------------------+
| max_login_attempts      | 5             | Maximum login attempts before lockout  |
| rate_limit_requests     | 100           | Maximum requests per IP per hour       |
| session_timeout_minutes | 120           | Session timeout in minutes             |
+-------------------------+---------------+----------------------------------------+
```

### 2. Loading at Application Start

In `/config/config.php` (lines 103-126), settings are loaded on every request:

```php
try {
    $db = getDatabaseConnection();

    // Get security settings from database
    $maxLoginAttempts = getSetting('max_login_attempts', MAX_LOGIN_ATTEMPTS);
    $rateLimitRequests = getSetting('rate_limit_requests', RATE_LIMIT_REQUESTS);
    $sessionTimeoutMinutes = getSetting('session_timeout_minutes', 120);

    // Define dynamic constants with database values
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
```

### 3. Usage Throughout Application

#### A. Authentication (Auth.php)

**Session Creation** (line 136):
```php
private function createSession($user) {
    $sessionId = bin2hex(random_bytes(32));
    $sessionLifetime = defined('DB_SESSION_LIFETIME') ? DB_SESSION_LIFETIME : SESSION_LIFETIME;
    $expiresAt = date('Y-m-d H:i:s', time() + $sessionLifetime);
    // ...
}
```

**Login Attempts Check** (lines 176-177):
```php
private function checkLoginAttempts($contact) {
    // ... count failed attempts ...

    $maxAttempts = defined('DB_MAX_LOGIN_ATTEMPTS') ? DB_MAX_LOGIN_ATTEMPTS : MAX_LOGIN_ATTEMPTS;
    return $result['count'] < $maxAttempts;
}
```

#### B. Rate Limiting (middleware/rate_limiter.php)

**Rate Limit Check** (lines 132-137):
```php
function checkRateLimit($identifier = null, $action = 'api', $maxAttempts = null, $timeWindow = null) {
    // Use database settings with fallback to hardcoded constants
    if ($maxAttempts === null) {
        $maxAttempts = defined('DB_RATE_LIMIT_REQUESTS') ? DB_RATE_LIMIT_REQUESTS :
                      (defined('RATE_LIMIT_REQUESTS') ? RATE_LIMIT_REQUESTS : 100);
    }
    if ($timeWindow === null) {
        $timeWindow = defined('RATE_LIMIT_WINDOW') ? RATE_LIMIT_WINDOW : 3600;
    }
    // ...
}
```

---

## Configuration via Settings Page

### Accessing Settings

1. Navigate to **Settings** page (admin only)
2. Scroll to **Security Settings** card (red/danger color)
3. Modify values as needed
4. Click **Save All Settings**

### Settings Interface

```
┌──────────────────────────────────────────────┐
│ 🛡️ Security Settings                         │
├──────────────────────────────────────────────┤
│                                              │
│ Max Login Attempts ⓘ                        │
│ ┌────────┐                                  │
│ │   5    │ [Number field]                   │
│ └────────┘                                  │
│                                              │
│ Rate Limit Requests ⓘ                       │
│ ┌────────┐                                  │
│ │  100   │ [Number field]                   │
│ └────────┘                                  │
│                                              │
│ Session Timeout Minutes ⓘ                   │
│ ┌────────┐                                  │
│ │  120   │ [Number field]                   │
│ └────────┘                                  │
│                                              │
│                   [Save All Settings] →      │
└──────────────────────────────────────────────┘
```

**Note:** ⓘ icons show tooltips with descriptions on hover

---

## Constants Reference

### Hardcoded Constants (Fallback)

These are defined in `/config/config.php` and serve as fallback values:

```php
define('SESSION_LIFETIME', 7200);        // 2 hours in seconds
define('MAX_LOGIN_ATTEMPTS', 5);         // 5 attempts
define('RATE_LIMIT_REQUESTS', 100);      // 100 requests
define('RATE_LIMIT_WINDOW', 3600);       // 1 hour in seconds
```

### Dynamic Constants (Database-Loaded)

These are created at runtime from database settings:

```php
define('DB_SESSION_LIFETIME', 7200);      // From session_timeout_minutes * 60
define('DB_MAX_LOGIN_ATTEMPTS', 5);       // From max_login_attempts
define('DB_RATE_LIMIT_REQUESTS', 100);    // From rate_limit_requests
```

---

## Fallback Mechanism

The system implements a multi-level fallback strategy:

### Level 1: Database Settings (Primary)
```php
DB_MAX_LOGIN_ATTEMPTS → Database value
```

### Level 2: Hardcoded Constants (Fallback)
```php
MAX_LOGIN_ATTEMPTS → Hardcoded in config.php
```

### Level 3: Code Default (Ultimate Fallback)
```php
5 → Hardcoded in specific functions
```

### Example Flow

```php
// In Auth::checkLoginAttempts()
$maxAttempts = defined('DB_MAX_LOGIN_ATTEMPTS')  // Try database constant
                   ? DB_MAX_LOGIN_ATTEMPTS
                   : MAX_LOGIN_ATTEMPTS;         // Fallback to hardcoded
```

---

## Testing

### Test Script

Run the test script to verify integration:

```bash
php test_security_settings.php
```

### Expected Output

```
=== TESTING SECURITY SETTINGS INTEGRATION ===

1. Hardcoded Constants (from config.php):
   SESSION_LIFETIME: 7200 seconds (120 minutes)
   MAX_LOGIN_ATTEMPTS: 5
   RATE_LIMIT_REQUESTS: 100
   RATE_LIMIT_WINDOW: 3600 seconds (1 hours)

2. Database Settings:
   max_login_attempts: 5
   rate_limit_requests: 100
   session_timeout_minutes: 120

3. Dynamic Constants (loaded from database):
   DB_MAX_LOGIN_ATTEMPTS: 5
   DB_RATE_LIMIT_REQUESTS: 100
   DB_SESSION_LIFETIME: 7200 seconds (120 minutes)

6. Comparison:
   Database -> Dynamic Constant Mapping:
   max_login_attempts (5) -> DB_MAX_LOGIN_ATTEMPTS (5): ✅ MATCH
   rate_limit_requests (100) -> DB_RATE_LIMIT_REQUESTS (100): ✅ MATCH
   session_timeout_minutes (120) -> DB_SESSION_LIFETIME (120 minutes): ✅ MATCH

✅ SUMMARY:
   - Security settings are loaded from database
   - Dynamic constants (DB_*) are created with database values
   - Auth class uses DB_SESSION_LIFETIME and DB_MAX_LOGIN_ATTEMPTS
   - Rate limiter uses DB_RATE_LIMIT_REQUESTS
   - All components have fallback to hardcoded constants if database fails
```

---

## Use Cases

### Use Case 1: Increase Session Timeout

**Scenario:** Teachers need longer sessions (3 hours instead of 2 hours)

**Steps:**
1. Go to Settings page
2. Change **Session Timeout Minutes** from 120 to 180
3. Click **Save All Settings**
4. Users logging in after this change will get 3-hour sessions

**Database Update:**
```sql
UPDATE settings
SET setting_value = '180'
WHERE setting_key = 'session_timeout_minutes';
```

**Effect:**
- New constant: `DB_SESSION_LIFETIME = 10800` (180 × 60)
- New sessions created with 3-hour expiry
- Existing sessions remain unchanged until expiry

### Use Case 2: Tighten Security During Attacks

**Scenario:** System is experiencing brute force login attempts

**Steps:**
1. Go to Settings page
2. Change **Max Login Attempts** from 5 to 3
3. Change **Rate Limit Requests** from 100 to 50
4. Click **Save All Settings**

**Effect:**
- Lockout after 3 failed attempts instead of 5
- API rate limit reduced to 50 requests/hour
- Changes take effect immediately for new requests

### Use Case 3: Relax Limits for Testing

**Scenario:** Testing environment needs relaxed limits

**Steps:**
1. Increase **Max Login Attempts** to 10
2. Increase **Rate Limit Requests** to 500
3. Increase **Session Timeout** to 480 (8 hours)

**Effect:**
- More tolerant of failed logins during testing
- Higher API request limits
- Longer sessions for extended testing

---

## Security Benefits

1. **✅ Centralized Control** - All security settings in one place
2. **✅ No Code Changes** - Adjust security without modifying files
3. **✅ Audit Trail** - All setting changes logged in audit_logs
4. **✅ Immediate Effect** - Changes apply on next request
5. **✅ Resilient** - Fallback to safe defaults if database fails
6. **✅ Flexible** - Different settings for different environments

---

## File Changes Summary

### Modified Files

1. **`/config/config.php`**
   - Added dynamic settings loading (lines 103-126)
   - Creates DB_* constants from database

2. **`/classes/Auth.php`**
   - Updated `createSession()` to use `DB_SESSION_LIFETIME` (line 136)
   - Updated `checkLoginAttempts()` to use `DB_MAX_LOGIN_ATTEMPTS` (line 176)

3. **`/middleware/rate_limiter.php`**
   - Updated `checkRateLimit()` to use `DB_RATE_LIMIT_REQUESTS` (lines 132-137)

### New Files

1. **`test_security_settings.php`** - Test script to verify integration

---

## Troubleshooting

### Issue: Settings Not Applied

**Symptoms:** Changed settings in UI but behavior unchanged

**Possible Causes:**
1. Database update failed
2. Caching issue
3. Wrong constant being used in code

**Solution:**
```bash
# 1. Verify database value
mysql -u root -p intervention_db -e "SELECT * FROM settings WHERE setting_key='max_login_attempts'"

# 2. Run test script
php test_security_settings.php

# 3. Check error logs
tail -f /var/log/apache2/error.log
```

### Issue: Database Connection Failed

**Symptoms:** Application uses hardcoded constants instead of database values

**Cause:** Database connection error during settings loading

**Evidence:** Error log shows "Failed to load dynamic settings"

**Solution:**
- Check database connection
- Verify `getSetting()` function works
- System will automatically use fallback constants (safe operation)

---

## API Response Headers

When settings are applied, you can verify them in API responses:

### Session Creation
```
Set-Cookie: PHPSESSID=...; Max-Age=7200  (uses DB_SESSION_LIFETIME)
```

### Rate Limiting
```
HTTP/1.1 429 Too Many Requests
Retry-After: 3600
X-RateLimit-Limit: 100            (uses DB_RATE_LIMIT_REQUESTS)
X-RateLimit-Remaining: 0
```

### Login Lockout
```json
{
  "success": false,
  "message": "Too many login attempts. Please try again in 15 minutes."
}
```
(Triggered after DB_MAX_LOGIN_ATTEMPTS failed attempts)

---

## Best Practices

1. **Production Values:**
   - Max Login Attempts: 3-5
   - Rate Limit: 100-500 per hour
   - Session Timeout: 30-120 minutes

2. **Development Values:**
   - Max Login Attempts: 10
   - Rate Limit: 1000 per hour
   - Session Timeout: 480 minutes (8 hours)

3. **After Changes:**
   - Test with a non-admin account first
   - Monitor audit logs
   - Document reason for changes

4. **Emergency Lockdown:**
   - Reduce max_login_attempts to 1
   - Reduce rate_limit_requests to 10
   - Reduce session_timeout to 15 minutes

---

## Technical Notes

### Why Both Hardcoded and Database Settings?

**Hardcoded Constants (MAX_LOGIN_ATTEMPTS, etc.):**
- Serve as safe defaults
- Used when database is unavailable
- Prevent system failure

**Database Settings (DB_MAX_LOGIN_ATTEMPTS, etc.):**
- Allow runtime configuration
- Can be changed without code deployment
- Provide flexibility

### Constants vs Variables

We use constants (not variables) because:
1. Defined once per request
2. Immutable during request lifecycle
3. Can use `defined()` to check existence
4. Better performance than repeated database queries

### Why getSetting() Has Fallback

```php
$maxLoginAttempts = getSetting('max_login_attempts', MAX_LOGIN_ATTEMPTS);
```

The second parameter provides fallback if:
- Setting doesn't exist in database
- Database query fails
- Setting value is NULL/empty

---

## Conclusion

The security settings integration provides flexible, database-driven security configuration while maintaining safe fallback behavior. Administrators can now adjust security parameters through the Settings UI without requiring code changes or deployments.

**Last Updated:** 2025-11-08
**Status:** ✅ Production Ready
**Tested:** ✅ All tests passing

---

## Related Documentation

- `AUTO_FEE_ASSIGNMENT.md` - Automatic fee assignment feature
- `DATABASE_RESTRUCTURE_2025-11-08.md` - Students table restructure
- Settings page implementation in `/pages/settings/index.php`
