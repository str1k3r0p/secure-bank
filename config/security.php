<?php
/**
 * Banking DVWA Project
 * Security Configuration
 * 
 * This file contains security-related settings and configurations.
 */

// Prevent direct access to this file
if (!defined('ROOT_PATH')) {
    die('Direct access to this file is not allowed.');
}

// CSRF protection
define('CSRF_TOKEN_NAME', 'csrf_token');                 // CSRF token name
define('CSRF_TOKEN_LENGTH', 32);                         // CSRF token length
define('CSRF_EXPIRATION', 3600);                         // CSRF token expiration (in seconds)

// Session security
define('SESSION_NAME', 'BANK_DVWA_SESSION');             // Session name
define('SESSION_EXPIRATION', 1800);                      // Session expiration (in seconds)
define('SESSION_SECURE', false);                         // Use secure cookies (should be true in production)
define('SESSION_HTTP_ONLY', true);                       // HTTP only cookies
define('SESSION_REGENERATE_ID', true);                   // Regenerate session ID

// Password hashing
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);           // Password hashing algorithm
define('PASSWORD_HASH_COST', 12);                        // Password hashing cost

// Security headers
define('SECURITY_HEADERS', [
    'X-Content-Type-Options' => 'nosniff',
    'X-Frame-Options' => 'SAMEORIGIN',
    'X-XSS-Protection' => '1; mode=block'
]);

// Content Security Policy (CSP)
// Note: Deliberately relaxed for vulnerability demonstrations
define('CONTENT_SECURITY_POLICY', "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self';");

// Vulnerability security levels
define('SECURITY_LEVELS', ['low', 'medium', 'high']);

// Default security level for each vulnerability
$default_security_levels = [
    'brute_force' => DEFAULT_SECURITY_LEVEL,
    'cmd_injection' => DEFAULT_SECURITY_LEVEL,
    'sql_injection' => DEFAULT_SECURITY_LEVEL,
    'directory_traversal' => DEFAULT_SECURITY_LEVEL,
    'xss' => DEFAULT_SECURITY_LEVEL
];

define('DEFAULT_SECURITY_LEVELS', $default_security_levels);

// API rate limiting
define('RATE_LIMIT_ENABLED', true);                      // Enable rate limiting
define('RATE_LIMIT_MAX_REQUESTS', 100);                  // Maximum requests per period
define('RATE_LIMIT_PERIOD', 60);                         // Period in seconds