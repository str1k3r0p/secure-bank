<?php
/**
 * Banking DVWA Project
 * Database Configuration
 * 
 * This file contains database connection settings.
 */

// Prevent direct access to this file
if (!defined('ROOT_PATH')) {
    die('Direct access to this file is not allowed.');
}

// Database configuration
define('DB_HOST', 'localhost');           // Database host
define('DB_NAME', 'bank_dvwa');           // Database name
define('DB_USER', 'root');                // Database username (default for XAMPP)
define('DB_PASS', '');                    // Database password (default for XAMPP is empty)
define('DB_CHARSET', 'utf8mb4');          // Database charset
define('DB_PORT', '3306');                // Database port
define('DB_PREFIX', 'bdv_');              // Table prefix

// PDO connection options
define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
]);

// Connection string for PDO
define('DB_DSN', 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET . ';port=' . DB_PORT);