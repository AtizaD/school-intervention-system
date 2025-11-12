<?php
/**
 * Helper Functions
 * Common utility functions used throughout the application
 */

/**
 * Generate unique receipt number
 */
function generateReceiptNumber() {
    $prefix = 'RCP';
    $timestamp = date('ymd');
    $random = strtoupper(substr(uniqid(), -4));
    return $prefix . $timestamp . $random;
}

/**
 * Generate unique student ID/username
 * This is a legacy function - use Student->generateUsername() instead
 * Format: {CLASS}-{INITIALS}{SEQUENCE}
 * Example: 1A-JD001, 2B-MKA002
 */
function generateStudentUsername($firstName = null, $lastName = null, $class = null) {
    if ($firstName && $lastName && $class) {
        $studentModel = new Student();
        $result = $studentModel->generateUsername($firstName, $lastName, $class);
        return $result['success'] ? $result['username'] : null;
    }

    // Fallback for legacy calls
    $year = date('y');
    $random = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    return 'STU' . $year . $random;
}

/**
 * Get age from date of birth
 */
function getAge($dateOfBirth) {
    $dob = new DateTime($dateOfBirth);
    $now = new DateTime();
    return $now->diff($dob)->y;
}

/**
 * Format file size
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));

    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Validate Ghana phone number
 */
function isValidGhanaPhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return strlen($phone) === 10 && substr($phone, 0, 1) === '0';
}

/**
 * Format Ghana phone number for display
 */
function formatGhanaPhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone) === 10) {
        return substr($phone, 0, 3) . ' ' . substr($phone, 3, 3) . ' ' . substr($phone, 6);
    }
    return $phone;
}

/**
 * Get academic term from date
 */
function getAcademicTerm($date = null) {
    $month = $date ? date('n', strtotime($date)) : date('n');

    if ($month >= 1 && $month <= 4) {
        return 'Term 2';
    } elseif ($month >= 5 && $month <= 8) {
        return 'Term 3';
    } else {
        return 'Term 1';
    }
}

/**
 * Get academic year from date
 */
function getAcademicYear($date = null) {
    $month = $date ? date('n', strtotime($date)) : date('n');
    $year = $date ? date('Y', strtotime($date)) : date('Y');

    if ($month >= 9) {
        return $year . '/' . ($year + 1);
    } else {
        return ($year - 1) . '/' . $year;
    }
}

/**
 * Calculate fee balance
 */
function calculateBalance($totalDue, $totalPaid) {
    return max(0, $totalDue - $totalPaid);
}

/**
 * Check if fee is overdue
 */
function isOverdue($dueDate, $balance) {
    if ($balance <= 0) {
        return false;
    }

    $due = new DateTime($dueDate);
    $now = new DateTime();

    return $now > $due;
}

/**
 * Get days overdue
 */
function getDaysOverdue($dueDate) {
    $due = new DateTime($dueDate);
    $now = new DateTime();

    if ($now <= $due) {
        return 0;
    }

    return $now->diff($due)->days;
}

/**
 * Get payment status badge color
 */
function getPaymentStatusColor($status) {
    $colors = [
        FEE_STATUS_PAID => 'success',
        FEE_STATUS_PARTIAL => 'warning',
        FEE_STATUS_PENDING => 'danger',
        'overdue' => 'dark'
    ];

    return $colors[$status] ?? 'secondary';
}

/**
 * Get role badge color
 */
function getRoleBadgeColor($role) {
    return $role === ROLE_ADMIN ? 'danger' : 'info';
}

/**
 * Truncate text
 */
function truncate($text, $length = 50, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }

    return substr($text, 0, $length - strlen($suffix)) . $suffix;
}

/**
 * Get initials from name
 */
function getInitials($name) {
    $words = explode(' ', $name);
    $initials = '';

    foreach ($words as $word) {
        if (!empty($word)) {
            $initials .= strtoupper($word[0]);
        }
    }

    return $initials;
}

/**
 * Generate random password
 */
function generateRandomPassword($length = 12) {
    $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $lowercase = 'abcdefghijklmnopqrstuvwxyz';
    $numbers = '0123456789';
    $special = '!@#$%^&*()';

    $password = '';
    $password .= $uppercase[rand(0, strlen($uppercase) - 1)];
    $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
    $password .= $numbers[rand(0, strlen($numbers) - 1)];
    $password .= $special[rand(0, strlen($special) - 1)];

    $all = $uppercase . $lowercase . $numbers . $special;
    for ($i = 4; $i < $length; $i++) {
        $password .= $all[rand(0, strlen($all) - 1)];
    }

    return str_shuffle($password);
}

/**
 * Check if request is AJAX
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Get client IP address
 */
function getClientIP() {
    $ipaddress = '';

    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    } else {
        $ipaddress = 'UNKNOWN';
    }

    return $ipaddress;
}

/**
 * Safe array get with default value
 */
function array_get($array, $key, $default = null) {
    return isset($array[$key]) ? $array[$key] : $default;
}

/**
 * Check if array is associative
 */
function is_assoc($array) {
    if (!is_array($array) || empty($array)) {
        return false;
    }

    return array_keys($array) !== range(0, count($array) - 1);
}

/**
 * Convert array to CSV
 */
function array_to_csv($array, $filename = 'export.csv') {
    if (empty($array)) {
        return false;
    }

    $output = fopen('php://output', 'w');

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    // Headers
    if (is_assoc($array[0])) {
        fputcsv($output, array_keys($array[0]));
    }

    // Data rows
    foreach ($array as $row) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit;
}

/**
 * Debug variable dump
 */
function dd($var) {
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
    die();
}

/**
 * Get setting value from database
 */
function getSetting($key, $default = null) {
    $db = new Database();
    $db->query("SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1");
    $db->bind(':key', $key);
    $result = $db->fetch();

    return $result ? $result['setting_value'] : $default;
}

/**
 * Update setting value
 */
function updateSetting($key, $value) {
    $db = new Database();
    $updated = $db->update('settings',
        ['setting_value' => $value, 'updated_at' => date('Y-m-d H:i:s')],
        'setting_key = :key',
        [':key' => $key]
    );

    if ($updated) {
        logActivity('UPDATE', 'settings', $key);
    }

    return $updated;
}

/**
 * Convert number to words (English)
 * Useful for receipts and official documents
 */
function numberToWords($number) {
    $number = (float) $number;

    // Handle decimals (for currency)
    $decimal = round(($number - floor($number)) * 100);
    $number = floor($number);

    $ones = [
        '', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine',
        'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen',
        'seventeen', 'eighteen', 'nineteen'
    ];

    $tens = [
        '', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'
    ];

    $scales = ['', 'thousand', 'million', 'billion', 'trillion'];

    if ($number == 0) {
        return 'zero';
    }

    $words = [];
    $scale = 0;

    while ($number > 0) {
        $chunk = $number % 1000;

        if ($chunk > 0) {
            $chunkWords = [];

            // Hundreds
            $hundreds = floor($chunk / 100);
            if ($hundreds > 0) {
                $chunkWords[] = $ones[$hundreds];
                $chunkWords[] = 'hundred';
            }

            // Tens and ones
            $remainder = $chunk % 100;
            if ($remainder >= 20) {
                $chunkWords[] = $tens[floor($remainder / 10)];
                if ($remainder % 10 > 0) {
                    $chunkWords[] = $ones[$remainder % 10];
                }
            } elseif ($remainder > 0) {
                $chunkWords[] = $ones[$remainder];
            }

            // Add scale
            if ($scale > 0) {
                $chunkWords[] = $scales[$scale];
            }

            $words = array_merge($chunkWords, $words);
        }

        $number = floor($number / 1000);
        $scale++;
    }

    $result = implode(' ', $words);

    // Add decimal part for currency
    if ($decimal > 0) {
        $result .= ' and ' . $decimal . '/100';
    }

    return $result;
}
