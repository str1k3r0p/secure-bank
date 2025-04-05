<?php
/**
 * Banking DVWA Project
 * Security Helper Functions
 * 
 * This file contains helper functions for security-related tasks.
 */

// Prevent direct access to this file
if (!defined('ROOT_PATH')) {
    die('Direct access to this file is not allowed.');
}

/**
 * Sanitize user input to prevent XSS
 * 
 * @param mixed $input The input to sanitize
 * @return mixed The sanitized input
 */
function xss_clean($input)
{
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            $input[$key] = xss_clean($value);
        }
        return $input;
    }
    
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate a CSRF token
 * 
 * @return string The CSRF token
 */
function generate_csrf_token()
{
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
function verify_csrf_token($token)
{
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
 * Filter special characters from user input
 * 
 * @param string $input User input
 * @return string Filtered input
 */
function filter_special_chars($input)
{
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            $input[$key] = filter_special_chars($value);
        }
        return $input;
    }
    
    return preg_replace('/[^a-zA-Z0-9\s]/', '', $input);
}

/**
 * Hash a password
 * 
 * @param string $password The password to hash
 * @return string The hashed password
 */
function hash_password($password)
{
    return password_hash($password, PASSWORD_HASH_ALGO, [
        'cost' => PASSWORD_HASH_COST
    ]);
}

/**
 * Verify a password against a hash
 * 
 * @param string $password The password to verify
 * @param string $hash The hash to verify against
 * @return bool True if the password is valid, false otherwise
 */
function verify_password($password, $hash)
{
    return password_verify($password, $hash);
}

/**
 * Generate a random token
 * 
 * @param int $length Token length
 * @return string Random token
 */
function generate_random_token($length = 32)
{
    return bin2hex(random_bytes($length / 2));
}

/**
 * Check if rate limit is exceeded
 * 
 * @param string $key Rate limit key
 * @param int $max Maximum requests
 * @param int $period Period in seconds
 * @return bool True if rate limit is exceeded
 */
function is_rate_limited($key, $max = RATE_LIMIT_MAX_REQUESTS, $period = RATE_LIMIT_PERIOD)
{
    if (!RATE_LIMIT_ENABLED) {
        return false;
    }
    
    // Initialize rate limit session if needed
    if (!isset($_SESSION['rate_limits'])) {
        $_SESSION['rate_limits'] = [];
    }
    
    // Check if key exists in session
    if (!isset($_SESSION['rate_limits'][$key])) {
        $_SESSION['rate_limits'][$key] = [
            'count' => 1,
            'start_time' => time()
        ];
        return false;
    }
    
    $rate_limit = $_SESSION['rate_limits'][$key];
    $current_time = time();
    
    // Reset rate limit if period has passed
    if ($current_time - $rate_limit['start_time'] > $period) {
        $_SESSION['rate_limits'][$key] = [
            'count' => 1,
            'start_time' => $current_time
        ];
        return false;
    }
    
    // Increment count
    $_SESSION['rate_limits'][$key]['count']++;
    
    // Check if rate limit is exceeded
    return $_SESSION['rate_limits'][$key]['count'] > $max;
}

/**
 * Get client IP address
 * 
 * @return string Client IP address
 */
function get_client_ip()
{
    $keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    
    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            return $_SERVER[$key];
        }
    }
    
    return 'UNKNOWN';
}

/**
 * Validate a URL
 * 
 * @param string $url The URL to validate
 * @return bool True if URL is valid
 */
function is_valid_url($url)
{
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Validate an email address
 * 
 * @param string $email The email to validate
 * @return bool True if email is valid
 */
function is_valid_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate an IP address
 * 
 * @param string $ip The IP to validate
 * @param string $version IP version (ipv4, ipv6 or both)
 * @return bool True if IP is valid
 */
function is_valid_ip($ip, $version = 'both')
{
    switch ($version) {
        case 'ipv4':
            $flag = FILTER_FLAG_IPV4;
            break;
        case 'ipv6':
            $flag = FILTER_FLAG_IPV6;
            break;
        default:
            $flag = null;
            break;
    }
    
    return filter_var($ip, FILTER_VALIDATE_IP, $flag) !== false;
}

/**
 * Check if a string contains valid JSON
 * 
 * @param string $string The string to check
 * @return bool True if string contains valid JSON
 */
function is_valid_json($string)
{
    if (!is_string($string) || empty($string)) {
        return false;
    }
    
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}

/**
 * Sanitize a filename to make it safe for use
 * 
 * @param string $filename The filename to sanitize
 * @return string Sanitized filename
 */
function sanitize_filename($filename)
{
    // Remove path information and control characters
    $filename = basename($filename);
    
    // Remove any character that is not alphanumeric, underscore, dash, or dot
    $filename = preg_replace('/[^\w\-\.]/', '', $filename);
    
    // Make sure filename doesn't start with a dot (hidden file)
    $filename = ltrim($filename, '.');
    
    return $filename;
}

/**
 * Sanitize a path to prevent directory traversal
 * 
 * @param string $path The path to sanitize
 * @return string Sanitized path
 */
function sanitize_path($path)
{
    // Normalize directory separators to prevent multiple separators
    $path = preg_replace('~[\\\/]+~', DIRECTORY_SEPARATOR, $path);
    
    // Remove directory traversal sequences
    $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
    $absolutes = [];
    
    foreach ($parts as $part) {
        if ('.' == $part) {
            continue;
        }
        
        if ('..' == $part) {
            array_pop($absolutes);
        } else {
            $absolutes[] = $part;
        }
    }
    
    return implode(DIRECTORY_SEPARATOR, $absolutes);
}

/**
 * Check if a user is logged in
 * 
 * @return bool True if user is logged in
 */
function is_logged_in()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if a user has a specific role
 * 
 * @param string $role The role to check
 * @return bool True if user has role
 */
function has_role($role)
{
    return is_logged_in() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Set a flash message
 * 
 * @param string $type Message type (success, error, info, warning)
 * @param string $message The message text
 */
function set_flash_message($type, $message)
{
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get the flash message
 * 
 * @return array|null Flash message or null
 */
function get_flash_message()
{
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    
    return null;
}

/**
 * Generate a secure random password
 * 
 * @param int $length Password length
 * @return string Random password
 */
function generate_password($length = 12)
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+[]{}|;:,.<>?';
    $password = '';
    
    for ($i = 0; $i < $length; $i++) {
        $random_index = random_int(0, strlen($chars) - 1);
        $password .= $chars[$random_index];
    }
    
    return $password;
}
