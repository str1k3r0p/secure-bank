<?php
/**
 * Banking DVWA Project
 * Global Functions
 * 
 * This file contains global utility functions used throughout the application.
 */

// Prevent direct access to this file
if (!defined('ROOT_PATH')) {
    die('Direct access to this file is not allowed.');
}

/**
 * Sanitize user input
 * 
 * @param string $input The input to sanitize
 * @return string The sanitized input
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a URL
 * 
 * @param string $url The URL to redirect to
 * @param int $status The HTTP status code
 */
function redirect($url, $status = 302) {
    header('Location: ' . $url, true, $status);
    exit;
}

/**
 * Generate a random string
 * 
 * @param int $length The length of the string
 * @return string The random string
 */
function random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $random_string = '';
    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $random_string;
}

/**
 * Generate a CSRF token
 * 
 * @return string The CSRF token
 */
function generate_csrf_token() {
    $token = bin2hex(random_bytes(CSRF_TOKEN_LENGTH / 2));
    $_SESSION[CSRF_TOKEN_NAME] = [
        'token' => $token,
        'expires' => time() + CSRF_EXPIRATION
    ];
    return $token;
}

/**
 * Verify a CSRF token
 * 
 * @param string $token The token to verify
 * @return bool True if the token is valid, false otherwise
 */
function verify_csrf_token($token) {
    if (empty($_SESSION[CSRF_TOKEN_NAME]) || empty($token)) {
        return false;
    }
    
    $session_token = $_SESSION[CSRF_TOKEN_NAME]['token'];
    $expiration = $_SESSION[CSRF_TOKEN_NAME]['expires'];
    
    if (time() > $expiration) {
        return false;
    }
    
    return hash_equals($session_token, $token);
}

/**
 * Format currency
 * 
 * @param float $amount The amount to format
 * @param string $currency The currency code
 * @return string The formatted currency
 */
function format_currency($amount, $currency = DEFAULT_CURRENCY) {
    return CURRENCY_SYMBOL . number_format($amount, 2);
}

/**
 * Format date
 * 
 * @param string $date The date to format
 * @param string $format The format to use
 * @return string The formatted date
 */
function format_date($date, $format = DATE_FORMAT) {
    $datetime = new DateTime($date);
    return $datetime->format($format);
}

/**
 * Check if user is logged in
 * 
 * @return bool True if the user is logged in, false otherwise
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user has a specific role
 * 
 * @param string $role The role to check
 * @return bool True if the user has the role, false otherwise
 */
function has_role($role) {
    return is_logged_in() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Log a message to a file
 * 
 * @param string $message The message to log
 * @param string $level The log level
 * @param string $file The log file
 */
function log_message($message, $level = 'info', $file = LOG_FILE) {
    if (!LOG_ENABLED) {
        return;
    }
    
    $log_levels = ['debug', 'info', 'warning', 'error'];
    $log_level_index = array_search(LOG_LEVEL, $log_levels);
    $current_level_index = array_search($level, $log_levels);
    
    if ($current_level_index < $log_level_index) {
        return;
    }
    
    $timestamp = date(DATETIME_FORMAT);
    $log_message = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    
    error_log($log_message, 3, $file);
}

/**
 * Get the current URL
 * 
 * @return string The current URL
 */
function current_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];
    return "{$protocol}://{$host}{$uri}";
}

/**
 * Display a flash message
 * 
 * @param string $type The message type (success, error, info, warning)
 * @param string $message The message to display
 */
function set_flash_message($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get flash message
 * 
 * @return array|null The flash message or null if none exists
 */
function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $flash_message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash_message;
    }
    return null;
}

/**
 * Validate an email address
 * 
 * @param string $email The email address to validate
 * @return bool True if the email is valid, false otherwise
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Get the client IP address
 * 
 * @return string The client IP address
 */
function get_client_ip() {
    $keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    
    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            return $_SERVER[$key];
        }
    }
    
    return 'UNKNOWN';
}