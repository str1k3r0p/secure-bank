<?php
/**
 * Banking DVWA Project
 * Error Handler Class
 * 
 * This class handles errors and exceptions.
 */

namespace App\Core;

class ErrorHandler
{
    /**
     * Initialize the error handler
     */
    public static function init()
    {
        // Set error handler
        set_error_handler([self::class, 'handleError']);
        
        // Set exception handler
        set_exception_handler([self::class, 'handleException']);
        
        // Register shutdown function
        register_shutdown_function([self::class, 'handleShutdown']);
    }
    
    /**
     * Handle errors
     * 
     * @param int $errno The error number
     * @param string $errstr The error message
     * @param string $errfile The file where the error occurred
     * @param int $errline The line where the error occurred
     * @return bool True to prevent PHP's internal error handler
     */
    public static function handleError($errno, $errstr, $errfile, $errline)
    {
        // Only handle errors that match the error_reporting level
        if (!(error_reporting() & $errno)) {
            return false;
        }
        
        // Get error type as string
        $error_type = self::getErrorType($errno);
        
        // Log the error
        $error_message = sprintf(
            "[%s] %s in %s on line %d",
            $error_type,
            $errstr,
            $errfile,
            $errline
        );
        
        Logger::error($error_message, [
            'file' => $errfile,
            'line' => $errline,
            'type' => $error_type
        ]);
        
        // If in development mode, display the error
        if (APP_ENV === 'development') {
            echo "<div style='color:red;'>{$error_message}</div>";
        }
        
        // Don't execute PHP's internal error handler
        return true;
    }
    
    /**
     * Handle exceptions
     * 
     * @param \Throwable $exception The exception
     */
    public static function handleException($exception)
    {
        // Log the exception
        $error_message = sprintf(
            "Uncaught Exception: %s in %s on line %d",
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );
        
        Logger::error($error_message, [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);
        
        // Handle the exception based on its type
        if ($exception instanceof NotFoundException) {
            self::showError404($exception);
        } else {
            self::showError500($exception);
        }
        
        exit(1);
    }
    
    /**
     * Handle shutdown
     */
    public static function handleShutdown()
    {
        $error = error_get_last();
        
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            // Get error type as string
            $error_type = self::getErrorType($error['type']);
            
            // Log the error
            $error_message = sprintf(
                "[%s] %s in %s on line %d",
                $error_type,
                $error['message'],
                $error['file'],
                $error['line']
            );
            
            Logger::error($error_message, [
                'file' => $error['file'],
                'line' => $error['line'],
                'type' => $error_type
            ]);
            
            // Display error page
            self::showError500(null);
        }
    }
    
    /**
     * Show 404 error page
     * 
     * @param \Throwable $exception The exception
     */
    private static function showError404($exception)
    {
        http_response_code(404);
        
        // If in development mode, display detailed error
        if (APP_ENV === 'development') {
            echo "<h1>Page Not Found</h1>";
            echo "<p><strong>Message:</strong> " . htmlspecialchars($exception->getMessage()) . "</p>";
            echo "<p><strong>File:</strong> " . htmlspecialchars($exception->getFile()) . "</p>";
            echo "<p><strong>Line:</strong> " . $exception->getLine() . "</p>";
            echo "<h2>Stack Trace</h2>";
            echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
        } else {
            // In production, redirect to error page
            header('Location: ' . APP_URL . '/error/404');
        }
    }
    
    /**
     * Show 500 error page
     * 
     * @param \Throwable $exception The exception
     */
    private static function showError500($exception)
    {
        http_response_code(500);
        
        // If in development mode, display detailed error
        if (APP_ENV === 'development' && $exception !== null) {
            echo "<h1>Server Error</h1>";
            echo "<p><strong>Message:</strong> " . htmlspecialchars($exception->getMessage()) . "</p>";
            echo "<p><strong>File:</strong> " . htmlspecialchars($exception->getFile()) . "</p>";
            echo "<p><strong>Line:</strong> " . $exception->getLine() . "</p>";
            echo "<h2>Stack Trace</h2>";
            echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
        } else {
            // In production, redirect to error page
            header('Location: ' . APP_URL . '/error/500');
        }
    }
    
    /**
     * Get error type as string
     * 
     * @param int $errno The error number
     * @return string The error type
     */
    private static function getErrorType($errno)
    {
        switch ($errno) {
            case E_ERROR:
                return 'E_ERROR';
            case E_WARNING:
                return 'E_WARNING';
            case E_PARSE:
                return 'E_PARSE';
            case E_NOTICE:
                return 'E_NOTICE';
            case E_CORE_ERROR:
                return 'E_CORE_ERROR';
            case E_CORE_WARNING:
                return 'E_CORE_WARNING';
            case E_COMPILE_ERROR:
                return 'E_COMPILE_ERROR';
            case E_COMPILE_WARNING:
                return 'E_COMPILE_WARNING';
            case E_USER_ERROR:
                return 'E_USER_ERROR';
            case E_USER_WARNING:
                return 'E_USER_WARNING';
            case E_USER_NOTICE:
                return 'E_USER_NOTICE';
            case E_STRICT:
                return 'E_STRICT';
            case E_RECOVERABLE_ERROR:
                return 'E_RECOVERABLE_ERROR';
            case E_DEPRECATED:
                return 'E_DEPRECATED';
            case E_USER_DEPRECATED:
                return 'E_USER_DEPRECATED';
            default:
                return 'UNKNOWN';
        }
    }
}