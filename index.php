<?php
/**
 * Banking DVWA Project
 * Main Entry Point
 * 
 * This file serves as the entry point for the application.
 * It bootstraps the application and forwards requests to the appropriate controller.
 */

// Define the application root path
define('ROOT_PATH', __DIR__);

// Check if the project is installed
if (!file_exists(ROOT_PATH . '/config/database.php')) {
    // Redirect to installer if not installed
    header('Location: install/index.php');
    exit;
}

// Include the application initialization file
require_once 'app/init.php';

// Forward to public/index.php which handles the actual application logic
header('Location: public/index.php');
exit;