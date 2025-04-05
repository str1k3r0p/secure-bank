<?php
/**
 * Banking DVWA Project
 * Application Configuration
 * 
 * This file contains general application settings.
 */

// Prevent direct access to this file
if (!defined('ROOT_PATH')) {
    die('Direct access to this file is not allowed.');
}

// Application information
define('APP_NAME', 'Banking DVWA Project');        // Application name
define('APP_DESCRIPTION', 'A cyberpunk-themed banking website with intentionally vulnerable implementations'); // Description
define('APP_VERSION', '1.0.0');                    // Application version
define('APP_AUTHOR', 'Security Researcher');       // Application author
define('APP_EMAIL', 'info@example.com');           // Contact email

// Environment settings
define('APP_ENV', 'development');                  // Environment (development, production)
define('APP_DEBUG', true);                         // Debug mode
define('APP_TIMEZONE', 'UTC');                     // Default timezone
define('APP_LOCALE', 'en');                        // Default locale
define('APP_CHARSET', 'UTF-8');                    // Default charset

// URL settings
define('APP_URL', 'http://localhost/bank_dvwa_project'); // Application URL
define('APP_ROOT', '/bank_dvwa_project');          // Application root path in URL
define('APP_ADMIN_URL', APP_URL . '/admin');      // Admin URL

// File upload settings
define('UPLOAD_MAX_SIZE', 2 * 1024 * 1024);       // 2MB max file size
define('UPLOAD_ALLOWED_TYPES', [                  // Allowed file types
    'image/jpeg',
    'image/png',
    'image/gif',
    'application/pdf',
    'text/plain'
]);

// Pagination settings
define('ITEMS_PER_PAGE', 10);                     // Default items per page

// Logging settings
define('LOG_ENABLED', true);                      // Enable logging
define('LOG_LEVEL', 'debug');                     // Log level (debug, info, warning, error)
define('LOG_FILE', LOGS_PATH . '/app.log');       // Default log file