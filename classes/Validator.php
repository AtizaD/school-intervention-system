<?php
/**
 * Validator Class
 * Handles input validation
 */

class Validator {
    private $errors = [];

    /**
     * Validate required field
     */
    public function required($field, $value, $message = null) {
        if (empty($value) && $value !== '0') {
            $this->errors[$field] = $message ?? ucfirst($field) . ' is required.';
        }
        return $this;
    }

    /**
     * Validate minimum length
     */
    public function minLength($field, $value, $min, $message = null) {
        if (strlen($value) < $min) {
            $this->errors[$field] = $message ?? ucfirst($field) . " must be at least $min characters.";
        }
        return $this;
    }

    /**
     * Validate maximum length
     */
    public function maxLength($field, $value, $max, $message = null) {
        if (strlen($value) > $max) {
            $this->errors[$field] = $message ?? ucfirst($field) . " must not exceed $max characters.";
        }
        return $this;
    }

    /**
     * Validate email format
     */
    public function email($field, $value, $message = null) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message ?? 'Invalid email address.';
        }
        return $this;
    }

    /**
     * Validate phone number (Ghana format)
     */
    public function phone($field, $value, $message = null) {
        // Remove spaces and dashes
        $phone = preg_replace('/[\s\-]/', '', $value);

        // Check if it matches Ghana phone format (10 digits starting with 0)
        if (!preg_match('/^0[2-5][0-9]{8}$/', $phone)) {
            $this->errors[$field] = $message ?? 'Invalid phone number format. Use 10 digits starting with 0.';
        }
        return $this;
    }

    /**
     * Validate numeric value
     */
    public function numeric($field, $value, $message = null) {
        if (!is_numeric($value)) {
            $this->errors[$field] = $message ?? ucfirst($field) . ' must be a number.';
        }
        return $this;
    }

    /**
     * Validate minimum value
     */
    public function min($field, $value, $min, $message = null) {
        if ($value < $min) {
            $this->errors[$field] = $message ?? ucfirst($field) . " must be at least $min.";
        }
        return $this;
    }

    /**
     * Validate maximum value
     */
    public function max($field, $value, $max, $message = null) {
        if ($value > $max) {
            $this->errors[$field] = $message ?? ucfirst($field) . " must not exceed $max.";
        }
        return $this;
    }

    /**
     * Validate date format
     */
    public function date($field, $value, $message = null) {
        $date = DateTime::createFromFormat('Y-m-d', $value);
        if (!$date || $date->format('Y-m-d') !== $value) {
            $this->errors[$field] = $message ?? 'Invalid date format. Use YYYY-MM-DD.';
        }
        return $this;
    }

    /**
     * Validate value matches another field (e.g., password confirmation)
     */
    public function matches($field, $value, $matchValue, $matchField, $message = null) {
        if ($value !== $matchValue) {
            $this->errors[$field] = $message ?? ucfirst($field) . " must match $matchField.";
        }
        return $this;
    }

    /**
     * Validate value is in array
     */
    public function inArray($field, $value, $array, $message = null) {
        if (!in_array($value, $array)) {
            $this->errors[$field] = $message ?? 'Invalid ' . strtolower($field) . ' selected.';
        }
        return $this;
    }

    /**
     * Validate unique value in database
     */
    public function unique($field, $value, $table, $column, $excludeId = null, $message = null) {
        $db = new Database();
        $sql = "SELECT COUNT(*) as count FROM $table WHERE $column = :value";

        if ($excludeId) {
            $sql .= " AND id != :id";
        }

        $db->query($sql)->bind(':value', $value);

        if ($excludeId) {
            $db->bind(':id', $excludeId);
        }

        $result = $db->fetch();

        if ($result['count'] > 0) {
            $this->errors[$field] = $message ?? ucfirst($field) . ' already exists.';
        }

        return $this;
    }

    /**
     * Validate password strength
     */
    public function password($field, $value, $message = null) {
        $errors = [];

        if (strlen($value) < MIN_PASSWORD_LENGTH) {
            $errors[] = "at least " . MIN_PASSWORD_LENGTH . " characters";
        }

        if (PASSWORD_REQUIRE_UPPERCASE && !preg_match('/[A-Z]/', $value)) {
            $errors[] = "one uppercase letter";
        }

        if (PASSWORD_REQUIRE_LOWERCASE && !preg_match('/[a-z]/', $value)) {
            $errors[] = "one lowercase letter";
        }

        if (PASSWORD_REQUIRE_NUMBER && !preg_match('/[0-9]/', $value)) {
            $errors[] = "one number";
        }

        if (PASSWORD_REQUIRE_SPECIAL && !preg_match('/[^A-Za-z0-9]/', $value)) {
            $errors[] = "one special character";
        }

        if (!empty($errors)) {
            $this->errors[$field] = $message ?? 'Password must contain ' . implode(', ', $errors) . '.';
        }

        return $this;
    }

    /**
     * Check if validation passed
     */
    public function isValid() {
        return empty($this->errors);
    }

    /**
     * Get all validation errors
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Get first error message
     */
    public function getFirstError() {
        return !empty($this->errors) ? reset($this->errors) : null;
    }

    /**
     * Get error for specific field
     */
    public function getError($field) {
        return $this->errors[$field] ?? null;
    }

    /**
     * Clear all errors
     */
    public function clearErrors() {
        $this->errors = [];
        return $this;
    }
}
