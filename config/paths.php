<?php
/**
 * Banking DVWA Project
 * Path Configuration
 * 
 * This file defines the paths used throughout the application.
 */

// Prevent direct access to this file
if (!defined('ROOT_PATH')) {
    die('Direct access to this file is not allowed.');
}

// Application paths
define('APP_PATH', ROOT_PATH . '/app');                  // Application directory
define('PUBLIC_PATH', ROOT_PATH . '/public');            // Public directory
define('CONFIG_PATH', ROOT_PATH . '/config');            // Configuration directory
define('INCLUDES_PATH', ROOT_PATH . '/includes');        // Includes directory
define('LOGS_PATH', ROOT_PATH . '/logs');                // Logs directory
define('TEMP_PATH', ROOT_PATH . '/temp');                // Temporary directory
define('UPLOAD_PATH', TEMP_PATH . '/uploads');           // Uploads directory
define('CACHE_PATH', TEMP_PATH . '/cache');              // Cache directory
define('VULNERABILITY_PATH', ROOT_PATH . '/vulnerabilities'); // Vulnerabilities directory

// View paths
define('VIEW_PATH', APP_PATH . '/views');                // Views directory
define('LAYOUT_PATH', VIEW_PATH . '/layout');            // Layout templates directory

// Asset URLs (for use in views)
define('CSS_URL', APP_URL . '/public/css');              // CSS URL
define('JS_URL', APP_URL . '/public/js');                // JavaScript URL
define('IMAGES_URL', APP_URL . '/public/assets/images'); // Images URL
define('FONTS_URL', APP_URL . '/public/assets/fonts');   // Fonts URL