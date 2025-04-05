<?php
/**
 * Banking DVWA Project
 * Version Information
 * 
 * This file contains version-related information for the application.
 */

// Prevent direct access to this file
if (!defined('ROOT_PATH')) {
    die('Direct access to this file is not allowed.');
}

/**
 * Application version information
 */
define('APP_VERSION_MAJOR', 1);                 // Major version
define('APP_VERSION_MINOR', 0);                 // Minor version
define('APP_VERSION_PATCH', 0);                 // Patch version
define('APP_VERSION_RELEASE', 'alpha');         // Release type (alpha, beta, rc, stable)
define('APP_VERSION_STRING', sprintf(
    '%d.%d.%d-%s',
    APP_VERSION_MAJOR,
    APP_VERSION_MINOR,
    APP_VERSION_PATCH,
    APP_VERSION_RELEASE
));

/**
 * Build information
 */
define('APP_BUILD_DATE', '2025-03-22');         // Build date
define('APP_BUILD_NUMBER', '001');              // Build number

/**
 * Compatibility information
 */
define('APP_REQUIRED_PHP_VERSION', '7.4.0');    // Required PHP version
define('APP_RECOMMENDED_PHP_VERSION', '8.0.0'); // Recommended PHP version
define('APP_REQUIRED_MYSQL_VERSION', '5.7.0');  // Required MySQL version

/**
 * Returns full version information as an array
 * 
 * @return array The version information
 */
function get_version_info() {
    return [
        'version' => APP_VERSION_STRING,
        'major' => APP_VERSION_MAJOR,
        'minor' => APP_VERSION_MINOR,
        'patch' => APP_VERSION_PATCH,
        'release' => APP_VERSION_RELEASE,
        'build_date' => APP_BUILD_DATE,
        'build_number' => APP_BUILD_NUMBER,
        'required_php' => APP_REQUIRED_PHP_VERSION,
        'recommended_php' => APP_RECOMMENDED_PHP_VERSION,
        'required_mysql' => APP_REQUIRED_MYSQL_VERSION
    ];
}