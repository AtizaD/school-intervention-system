<?php
/**
 * Application Constants
 * Intervention - School Money Collection System
 */

// Prevent direct access
if (!defined('APP_ACCESS')) {
    die('Direct access not permitted');
}

// Currency
define('CURRENCY_CODE', 'GHS');
define('CURRENCY', 'GHS');
define('CURRENCY_NAME', 'Ghana Cedis');

// User roles
define('ROLE_ADMIN', 'admin');
define('ROLE_TEACHER', 'teacher');

// Payment methods
define('PAYMENT_CASH', 'cash');
define('PAYMENT_MOBILE_MONEY', 'mobile_money');
define('PAYMENT_OTHERS', 'others');

// Fee status
define('FEE_PENDING', 'pending');
define('FEE_PARTIAL', 'partial');
define('FEE_PAID', 'paid');
define('FEE_OVERDUE', 'overdue');

// Gender
define('GENDER_MALE', 'male');
define('GENDER_FEMALE', 'female');

// Houses
define('HOUSE_1', '1');
define('HOUSE_2', '2');
define('HOUSE_3', '3');
define('HOUSE_4', '4');

// Notification types
define('NOTIF_PAYMENT_REMINDER', 'payment_reminder');
define('NOTIF_PAYMENT_RECEIVED', 'payment_received');
define('NOTIF_GENERAL', 'general');

// Notification priorities
define('PRIORITY_LOW', 'low');
define('PRIORITY_MEDIUM', 'medium');
define('PRIORITY_HIGH', 'high');

// Delivery status
define('DELIVERY_PENDING', 'pending');
define('DELIVERY_SENT', 'sent');
define('DELIVERY_FAILED', 'failed');
define('DELIVERY_DELIVERED', 'delivered');

// Parent relationships
define('RELATION_FATHER', 'father');
define('RELATION_MOTHER', 'mother');
define('RELATION_GUARDIAN', 'guardian');
define('RELATION_OTHER', 'other');

// Classes/Grades
define('CLASSES', [
    'Nursery 1', 'Nursery 2',
    'KG 1', 'KG 2',
    'Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5', 'Class 6',
    'JHS 1', 'JHS 2', 'JHS 3'
]);

// Academic terms
define('TERMS', [
    '1' => 'First Term',
    '2' => 'Second Term',
    '3' => 'Third Term'
]);

// Success messages
define('MSG_LOGIN_SUCCESS', 'Login successful! Welcome back.');
define('MSG_LOGOUT_SUCCESS', 'You have been logged out successfully.');
define('MSG_CREATE_SUCCESS', 'Record created successfully.');
define('MSG_UPDATE_SUCCESS', 'Record updated successfully.');
define('MSG_DELETE_SUCCESS', 'Record deleted successfully.');
define('MSG_PAYMENT_SUCCESS', 'Payment recorded successfully.');

// Error messages
define('ERR_LOGIN_FAILED', 'Invalid contact number or password.');
define('ERR_ACCESS_DENIED', 'Access denied. You do not have permission to access this resource.');
define('ERR_SESSION_EXPIRED', 'Your session has expired. Please login again.');
define('ERR_INVALID_INPUT', 'Invalid input provided. Please check your data.');
define('ERR_DATABASE_ERROR', 'A database error occurred. Please try again.');
define('ERR_NOT_FOUND', 'The requested resource was not found.');
define('ERR_DUPLICATE_ENTRY', 'A record with this information already exists.');
