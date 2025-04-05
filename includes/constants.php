<?php
/**
 * Banking DVWA Project
 * Application Constants
 * 
 * This file defines constants used throughout the application.
 */

// Prevent direct access to this file
if (!defined('ROOT_PATH')) {
    die('Direct access to this file is not allowed.');
}

// User roles
define('ROLE_USER', 'user');                     // Regular user role
define('ROLE_ADMIN', 'admin');                   // Administrator role
define('ROLE_MANAGER', 'manager');               // Bank manager role

// Account types
define('ACCOUNT_SAVINGS', 'savings');            // Savings account
define('ACCOUNT_CHECKING', 'checking');          // Checking account
define('ACCOUNT_BUSINESS', 'business');          // Business account

// Transaction types
define('TRANSACTION_DEPOSIT', 'deposit');        // Deposit transaction
define('TRANSACTION_WITHDRAWAL', 'withdrawal');  // Withdrawal transaction
define('TRANSACTION_TRANSFER', 'transfer');      // Transfer transaction
define('TRANSACTION_PAYMENT', 'payment');        // Payment transaction

// Transaction status
define('STATUS_PENDING', 'pending');             // Pending status
define('STATUS_COMPLETED', 'completed');         // Completed status
define('STATUS_FAILED', 'failed');               // Failed status
define('STATUS_CANCELLED', 'cancelled');         // Cancelled status

// Currency
define('DEFAULT_CURRENCY', 'USD');               // Default currency
define('CURRENCY_SYMBOL', '$');                  // Currency symbol

// Date formats
define('DATE_FORMAT', 'Y-m-d');                  // Default date format
define('TIME_FORMAT', 'H:i:s');                  // Default time format
define('DATETIME_FORMAT', 'Y-m-d H:i:s');        // Default datetime format

// Security levels
define('SECURITY_LOW', 'low');                   // Low security level
define('SECURITY_MEDIUM', 'medium');             // Medium security level
define('SECURITY_HIGH', 'high');                 // High security level

// HTTP status codes
define('HTTP_OK', 200);                          // OK
define('HTTP_CREATED', 201);                     // Created
define('HTTP_NO_CONTENT', 204);                  // No Content
define('HTTP_BAD_REQUEST', 400);                 // Bad Request
define('HTTP_UNAUTHORIZED', 401);                // Unauthorized
define('HTTP_FORBIDDEN', 403);                   // Forbidden
define('HTTP_NOT_FOUND', 404);                   // Not Found
define('HTTP_METHOD_NOT_ALLOWED', 405);          // Method Not Allowed
define('HTTP_INTERNAL_SERVER_ERROR', 500);       // Internal Server Error