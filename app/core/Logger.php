<?php
/**
 * Banking DVWA Project
 * Logger Class
 * 
 * This class handles logging of application events.
 */

namespace App\Core;

class Logger
{
    /**
     * @var string The default log file
     */
    private static $defaultLogFile = LOG_FILE;
    
    /**
     * @var array Log levels
     */
    private static $levels = [
        'debug' => 0,
        'info' => 1,
        'warning' => 2,
        'error' => 3,
        'critical' => 4
    ];
    
    /**
     * Initialize the logger
     */
    public static function init()
    {
        // Create log directory if it doesn't exist
        if (!file_exists(LOGS_PATH)) {
            mkdir(LOGS_PATH, 0755, true);
        }
        
        // Create log files if they don't exist
        $log_files = [
            LOGS_PATH . '/app.log',
            LOGS_PATH . '/error.log',
            LOGS_PATH . '/access.log',
            LOGS_PATH . '/security.log'
        ];
        
        foreach ($log_files as $file) {
            if (!file_exists($file)) {
                touch($file);
                chmod($file, 0644);
            }
        }
    }
    
    /**
     * Log a debug message
     * 
     * @param string $message The message to log
     * @param array $context Additional context data
     */
    public static function debug($message, $context = [])
    {
        self::log('debug', $message, $context);
    }
    
    /**
     * Log an info message
     * 
     * @param string $message The message to log
     * @param array $context Additional context data
     */
    public static function info($message, $context = [])
    {
        self::log('info', $message, $context);
    }
    
    /**
     * Log a warning message
     * 
     * @param string $message The message to log
     * @param array $context Additional context data
     */
    public static function warning($message, $context = [])
    {
        self::log('warning', $message, $context);
    }
    
    /**
     * Log an error message
     * 
     * @param string $message The message to log
     * @param array $context Additional context data
     */
    public static function error($message, $context = [])
    {
        self::log('error', $message, $context, LOGS_PATH . '/error.log');
    }
    
    /**
     * Log a critical message
     * 
     * @param string $message The message to log
     * @param array $context Additional context data
     */
    public static function critical($message, $context = [])
    {
        self::log('critical', $message, $context, LOGS_PATH . '/error.log');
    }
    
    /**
     * Log a security event
     * 
     * @param string $message The message to log
     * @param array $context Additional context data
     */
    public static function security($message, $context = [])
    {
        self::log('info', $message, $context, LOGS_PATH . '/security.log');
    }
    
    /**
     * Log an access event
     * 
     * @param string $message The message to log
     * @param array $context Additional context data
     */
    public static function access($message, $context = [])
    {
        self::log('info', $message, $context, LOGS_PATH . '/access.log');
    }
    
    /**
     * Log a message
     * 
     * @param string $level The log level
     * @param string $message The message to log
     * @param array $context Additional context data
     * @param string $file The log file
     */
    public static function log($level, $message, $context = [], $file = null)
    {
        if (!LOG_ENABLED) {
            return;
        }
        
        // Check if the log level is enabled
        $log_level_index = self::$levels[LOG_LEVEL] ?? 0;
        $current_level_index = self::$levels[$level] ?? 0;
        
        if ($current_level_index < $log_level_index) {
            return;
        }
        
        // Set default log file if not provided
        if ($file === null) {
            $file = self::$defaultLogFile;
        }
        
        // Format the log message
        $timestamp = date(DATETIME_FORMAT);
        $context_str = '';
        
        if (!empty($context)) {
            $context_str = ' - ' . json_encode($context);
        }
        
        $log_message = "[{$timestamp}] [{$level}] {$message}{$context_str}" . PHP_EOL;
        
        // Write to log file
        error_log($log_message, 3, $file);
    }
    
    /**
     * Set the default log file
     * 
     * @param string $file The log file
     */
    public static function setDefaultLogFile($file)
    {
        self::$defaultLogFile = $file;
    }
    
    /**
     * Get the default log file
     * 
     * @return string The default log file
     */
    public static function getDefaultLogFile()
    {
        return self::$defaultLogFile;
    }
}