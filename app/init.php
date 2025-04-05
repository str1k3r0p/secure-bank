<?php
/**
 * Banking DVWA Project
 * Application Initialization
 * 
 * This file initializes the application, sets up error handling,
 * and performs other startup tasks.
 */

// Prevent direct access to this file
if (!defined('ROOT_PATH')) {
    die('Direct access to this file is not allowed.');
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Configure session
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);
    
    // Set session name
    session_name(SESSION_NAME);
    
    // Set session cookie parameters
    session_set_cookie_params([
        'lifetime' => SESSION_EXPIRATION,
        'path' => '/',
        'domain' => '',
        'secure' => SESSION_SECURE,
        'httponly' => SESSION_HTTP_ONLY,
        'samesite' => 'Lax'
    ]);
    
    // Start session
    session_start();
    
    // Regenerate session ID if configured
    if (SESSION_REGENERATE_ID && !isset($_SESSION['last_regeneration'])) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    } elseif (SESSION_REGENERATE_ID && time() - $_SESSION['last_regeneration'] > 300) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

// Set up custom error handling
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // Only log errors that match the error_reporting level
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    // Log the error
    $error_message = sprintf(
        "Error [%d]: %s in %s on line %d",
        $errno,
        $errstr,
        $errfile,
        $errline
    );
    
    log_message($error_message, 'error', LOGS_PATH . '/error.log');
    
    // If in development mode, display the error
    if (APP_ENV === 'development') {
        echo "<div style='color:red;'>{$error_message}</div>";
    }
    
    // Don't execute PHP's internal error handler
    return true;
});

// Set up exception handling
set_exception_handler(function($exception) {
    // Log the exception
    $error_message = sprintf(
        "Uncaught Exception: %s in %s on line %d\nStack trace: %s",
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    );
    
    log_message($error_message, 'error', LOGS_PATH . '/error.log');
    
    // If in development mode, display the exception
    if (APP_ENV === 'development') {
        echo "<h1>Uncaught Exception</h1>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($exception->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($exception->getFile()) . "</p>";
        echo "<p><strong>Line:</strong> " . $exception->getLine() . "</p>";
        echo "<h2>Stack Trace</h2>";
        echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
    } else {
        // In production, redirect to error page
        header('Location: ' . APP_URL . '/error/500');
    }
    
    exit(1);
});

// Register shutdown function to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // Log the error
        $error_message = sprintf(
            "Fatal Error [%d]: %s in %s on line %d",
            $error['type'],
            $error['message'],
            $error['file'],
            $error['line']
        );
        
        log_message($error_message, 'error', LOGS_PATH . '/error.log');
        
        // If in development mode, display the error
        if (APP_ENV === 'development') {
            echo "<div style='color:red;'>{$error_message}</div>";
        } else {
            // In production, redirect to error page
            header('Location: ' . APP_URL . '/error/500');
        }
    }
});

// Set security headers
foreach (SECURITY_HEADERS as $header => $value) {
    header("$header: $value");
}

// Set Content Security Policy
header("Content-Security-Policy: " . CONTENT_SECURITY_POLICY);

// Create required directories if they don't exist
$directories = [
    LOGS_PATH,
    TEMP_PATH,
    CACHE_PATH,
    UPLOAD_PATH
];

foreach ($directories as $directory) {
    if (!file_exists($directory)) {
        mkdir($directory, 0755, true);
    }
}

// Initialize the database connection
try {
    $db = \App\Core\Database::getInstance();
} catch (\Exception $e) {
    // Log database connection error
    log_message("Database connection error: " . $e->getMessage(), 'error', LOGS_PATH . '/error.log');
    
    // If installation is not complete, redirect to setup
    if (!file_exists(ROOT_PATH . '/config/installed.php')) {
        header('Location: ' . APP_URL . '/setup.php');
        exit;
    }
}

// Initialize the logger
\App\Core\Logger::init();

// Initialize the router
\App\Core\Router::init();