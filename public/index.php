<?php
/**
 * Banking DVWA Project
 * Public Entry Point
 * 
 * This file serves as the public entry point for the application.
 * It initializes the application and processes the request.
 */

// Define the application root path
define('ROOT_PATH', dirname(__DIR__));

// Include the application configuration
require_once ROOT_PATH . '/config.php';

// Check if the application is installed
if (!file_exists(ROOT_PATH . '/config/installed.php') && !in_array($_SERVER['REQUEST_URI'], ['/install/index.php', '/setup.php'])) {
    // Redirect to setup if not installed
    header('Location: ' . APP_URL . '/setup.php');
    exit;
}

// Get the current URL path
$request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
$base_path = str_replace('/public', '', dirname($_SERVER['SCRIPT_NAME']));
$path = str_replace($base_path, '', $request_uri);
$path = strtok($path, '?');

// Initialize the Router
$router = new \App\Core\Router();

// Process the request and load the appropriate controller
try {
    $router->process($path);
} catch (\Exception $e) {
    // Log the error
    \App\Core\Logger::error($e->getMessage(), ['trace' => $e->getTraceAsString()]);
    
    // Handle the error
    $error_controller = new \App\Controllers\ErrorController();
    
    if ($e instanceof \App\Core\NotFoundException) {
        $error_controller->notFound();
    } else {
        $error_controller->serverError();
    }
}