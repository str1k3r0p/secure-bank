<?php
/**
 * Banking DVWA Project
 * Global Configuration Settings
 * 
 * This file contains global configuration settings and includes other configuration files.
 * It sets up the environment for the application to run properly.
 */

// Prevent direct access to this file
if (!defined('ROOT_PATH')) {
    die('Direct access to this file is not allowed.');
}

// Define application environment (development, testing, production)
define('APP_ENV', 'development');

// Define application version
define('APP_VERSION', '1.0.0');

// Define application name
define('APP_NAME', 'Banking DVWA Project');

// Define application URL (change this according to your setup)
define('APP_URL', 'http://localhost/bank_dvwa_project');

// Define application timezone
define('APP_TIMEZONE', 'UTC');

// Define default security level for vulnerabilities
define('DEFAULT_SECURITY_LEVEL', 'low'); // Options: low, medium, high

// Error reporting settings
if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
}

// Set timezone
date_default_timezone_set(APP_TIMEZONE);

// Load additional configuration files
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/config/paths.php';
require_once ROOT_PATH . '/config/security.php';
require_once ROOT_PATH . '/config/routes.php';

// Include constants, functions, and autoloader
require_once ROOT_PATH . '/includes/constants.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/includes/autoload.php';