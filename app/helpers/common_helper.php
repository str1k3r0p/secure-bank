<?php
/**
 * Banking DVWA Project
 * Common Helper Functions
 * 
 * This file contains general helper functions used throughout the application.
 */

// Prevent direct access to this file
if (!defined('ROOT_PATH')) {
    die('Direct access to this file is not allowed.');
}

/**
 * Get a value from an array using dot notation
 * 
 * @param array $array The array to search
 * @param string $key The key to retrieve (dot notation supported)
 * @param mixed $default Default value if key doesn't exist
 * @return mixed The value
 */
function array_get($array, $key, $default = null)
{
    if (is_null($key)) {
        return $array;
    }
    
    if (isset($array[$key])) {
        return $array[$key];
    }
    
    foreach (explode('.', $key) as $segment) {
        if (!is_array($array) || !array_key_exists($segment, $array)) {
            return $default;
        }
        
        $array = $array[$segment];
    }
    
    return $array;
}

/**
 * Set a value in an array using dot notation
 * 
 * @param array &$array The array to modify
 * @param string $key The key to set (dot notation supported)
 * @param mixed $value The value to set
 * @return array The modified array
 */
function array_set(&$array, $key, $value)
{
    if (is_null($key)) {
        return $array = $value;
    }
    
    $keys = explode('.', $key);
    
    while (count($keys) > 1) {
        $key = array_shift($keys);
        
        if (!isset($array[$key]) || !is_array($array[$key])) {
            $array[$key] = [];
        }
        
        $array = &$array[$key];
    }
    
    $array[array_shift($keys)] = $value;
    
    return $array;
}

/**
 * Check if a key exists in an array using dot notation
 * 
 * @param array $array The array to search
 * @param string $key The key to check
 * @return bool True if key exists
 */
function array_has($array, $key)
{
    if (empty($array) || is_null($key)) {
        return false;
    }
    
    if (array_key_exists($key, $array)) {
        return true;
    }
    
    foreach (explode('.', $key) as $segment) {
        if (!is_array($array) || !array_key_exists($segment, $array)) {
            return false;
        }
        
        $array = $array[$segment];
    }
    
    return true;
}

/**
 * Get the first item from an array
 * 
 * @param array $array The array
 * @param mixed $default Default value if array is empty
 * @return mixed First item or default
 */
function array_first($array, $default = null)
{
    return !empty($array) ? reset($array) : $default;
}

/**
 * Get the last item from an array
 * 
 * @param array $array The array
 * @param mixed $default Default value if array is empty
 * @return mixed Last item or default
 */
function array_last($array, $default = null)
{
    return !empty($array) ? end($array) : $default;
}

/**
 * Get a random value from an array
 * 
 * @param array $array The array
 * @return mixed Random value
 */
function array_random($array)
{
    if (empty($array)) {
        return null;
    }
    
    $keys = array_keys($array);
    $random_key = $keys[array_rand($keys)];
    
    return $array[$random_key];
}

/**
 * Flatten a multi-dimensional array into a single level
 * 
 * @param array $array The array to flatten
 * @param int $depth Maximum depth to flatten
 * @return array Flattened array
 */
function array_flatten($array, $depth = INF)
{
    $result = [];
    
    foreach ($array as $item) {
        if (is_array($item) && $depth > 0) {
            foreach (array_flatten($item, $depth - 1) as $value) {
                $result[] = $value;
            }
        } else {
            $result[] = $item;
        }
    }
    
    return $result;
}

/**
 * Convert a string to snake_case
 * 
 * @param string $string The string to convert
 * @return string snake_case string
 */
function snake_case($string)
{
    if (!is_string($string)) {
        return $string;
    }
    
    $string = preg_replace('/\s+/u', '_', $string);
    $string = preg_replace('/(.)(?=[A-Z])/u', '$1_', $string);
    
    return strtolower($string);
}

/**
 * Convert a string to camelCase
 * 
 * @param string $string The string to convert
 * @return string camelCase string
 */
function camel_case($string)
{
    if (!is_string($string)) {
        return $string;
    }
    
    $string = ucwords(str_replace(['-', '_'], ' ', $string));
    $string = lcfirst(str_replace(' ', '', $string));
    
    return $string;
}

/**
 * Convert a string to StudlyCase
 * 
 * @param string $string The string to convert
 * @return string StudlyCase string
 */
function studly_case($string)
{
    if (!is_string($string)) {
        return $string;
    }
    
    $string = ucwords(str_replace(['-', '_'], ' ', $string));
    $string = str_replace(' ', '', $string);
    
    return $string;
}

/**
 * Check if a string starts with a specific substring
 * 
 * @param string $haystack The string to search in
 * @param string|array $needle The substring(s) to search for
 * @return bool True if string starts with substring
 */
function starts_with($haystack, $needle)
{
    if (!is_string($haystack)) {
        return false;
    }
    
    if (is_string($needle)) {
        return $needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }
    
    if (is_array($needle)) {
        foreach ($needle as $n) {
            if (starts_with($haystack, $n)) {
                return true;
            }
        }
    }
    
    return false;
}

/**
 * Check if a string ends with a specific substring
 * 
 * @param string $haystack The string to search in
 * @param string|array $needle The substring(s) to search for
 * @return bool True if string ends with substring
 */
function ends_with($haystack, $needle)
{
    if (!is_string($haystack)) {
        return false;
    }
    
    if (is_string($needle)) {
        return $needle !== '' && substr($haystack, -strlen($needle)) === $needle;
    }
    
    if (is_array($needle)) {
        foreach ($needle as $n) {
            if (ends_with($haystack, $n)) {
                return true;
            }
        }
    }
    
    return false;
}

/**
 * Check if a string contains a specific substring
 * 
 * @param string $haystack The string to search in
 * @param string|array $needle The substring(s) to search for
 * @return bool True if string contains substring
 */
function str_contains($haystack, $needle)
{
    if (!is_string($haystack)) {
        return false;
    }
    
    if (is_string($needle)) {
        return $needle !== '' && strpos($haystack, $needle) !== false;
    }
    
    if (is_array($needle)) {
        foreach ($needle as $n) {
            if (str_contains($haystack, $n)) {
                return true;
            }
        }
    }
    
    return false;
}

/**
 * Get a string between two substrings
 * 
 * @param string $string The string to search in
 * @param string $start The start substring
 * @param string $end The end substring
 * @return string String between substrings
 */
function str_between($string, $start, $end)
{
    if (!is_string($string) || !is_string($start) || !is_string($end)) {
        return '';
    }
    
    $start_pos = strpos($string, $start);
    
    if ($start_pos === false) {
        return '';
    }
    
    $start_pos += strlen($start);
    $end_pos = strpos($string, $end, $start_pos);
    
    if ($end_pos === false) {
        return '';
    }
    
    return substr($string, $start_pos, $end_pos - $start_pos);
}

/**
 * Generate a random string
 * 
 * @param int $length String length
 * @param string $characters Characters to use
 * @return string Random string
 */
function random_string($length = 10, $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
{
    $randomString = '';
    $charactersLength = strlen($characters);
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    
    return $randomString;
}

/**
 * Generate a UUID v4
 * 
 * @return string UUID
 */
function generate_uuid()
{
    $data = random_bytes(16);
    
    // Set version to 4
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    // Set variant to RFC 4122
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

/**
 * Log a message to a file
 * 
 * @param string $message The message to log
 * @param string $level Log level (debug, info, warning, error)
 * @param string $file Log file path
 */
function log_message($message, $level = 'info', $file = null)
{
    if (!LOG_ENABLED) {
        return;
    }
    
    // Set default file if not provided
    if ($file === null) {
        $file = LOG_FILE;
    }
    
    // Create log directory if it doesn't exist
    $dir = dirname($file);
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
    
    // Format the log message
    $timestamp = date(DATETIME_FORMAT);
    $log_message = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    
    // Write to log file
    error_log($log_message, 3, $file);
}

/**
 * Check if a directory is writable or can be created
 * 
 * @param string $dir Directory path
 * @return bool True if directory is writable
 */
function is_dir_writable($dir)
{
    if (file_exists($dir)) {
        return is_writable($dir);
    }
    
    $parent = dirname($dir);
    if (!file_exists($parent)) {
        return is_dir_writable($parent);
    }
    
    return is_writable($parent);
}

/**
 * Get human-readable time difference
 * 
 * @param string $datetime Date and time
 * @param string $referenceTime Reference time (default: now)
 * @param bool $full Show full units
 * @return string Time difference
 */
function time_diff($datetime, $referenceTime = null, $full = false)
{
    if (!$referenceTime) {
        $referenceTime = date('Y-m-d H:i:s');
    }
    
    $now = new \DateTime($referenceTime);
    $ago = new \DateTime($datetime);
    $diff = $now->diff($ago);
    
    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;
    
    $string = [
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    ];
    
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }
    
    if (!$full) {
        $string = array_slice($string, 0, 1);
    }
    
    return $string ? implode(', ', $string) . ($now > $ago ? ' ago' : ' from now') : 'just now';
}

/**
 * Calculate percentage
 * 
 * @param float $value The value
 * @param float $total The total
 * @param int $decimals Number of decimal places
 * @return float Percentage
 */
function calculate_percentage($value, $total, $decimals = 2)
{
    if ($total == 0) {
        return 0;
    }
    
    return round(($value / $total) * 100, $decimals);
}

/**
 * Create a directory if it doesn't exist
 * 
 * @param string $dir Directory path
 * @param int $permissions Directory permissions
 * @return bool True if directory exists or was created
 */
function make_dir($dir, $permissions = 0755)
{
    if (file_exists($dir)) {
        return is_dir($dir);
    }
    
    return mkdir($dir, $permissions, true);
}

/**
 * Get file extension
 * 
 * @param string $filename Filename
 * @return string File extension
 */
function get_file_extension($filename)
{
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Check if a file has an allowed extension
 * 
 * @param string $filename Filename
 * @param array $allowed_extensions Allowed extensions
 * @return bool True if file extension is allowed
 */
function has_allowed_extension($filename, $allowed_extensions = [])
{
    $extension = get_file_extension($filename);
    return in_array($extension, $allowed_extensions);
}

/**
 * Return a default value if the given value is empty
 * 
 * @param mixed $value The value to check
 * @param mixed $default Default value
 * @return mixed Original value or default
 */
function value_or_default($value, $default = '')
{
    return !empty($value) ? $value : $default;
}

/**
 * Debug function to print variables with formatting
 * 
 * @param mixed $var Variable to debug
 * @param bool $die Stop execution after output
 */
function debug($var, $die = false)
{
    echo '<pre>';
    print_r($var);
    echo '</pre>';
    
    if ($die) {
        die();
    }
}

/**
 * Get the memory usage in a readable format
 * 
 * @param bool $real_usage Get real usage
 * @return string Memory usage
 */
function memory_usage($real_usage = false)
{
    $memory = memory_get_usage($real_usage);
    $unit = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
    
    return @round($memory / pow(1024, ($i = floor(log($memory, 1024)))), 2) . ' ' . $unit[$i];
}

/**
 * Get the execution time
 * 
 * @return float Execution time in seconds
 */
function execution_time()
{
    return microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
}

/**
 * Convert a value to boolean
 * 
 * @param mixed $value The value to convert
 * @return bool Boolean value
 */
function to_boolean($value)
{
    if (is_bool($value)) {
        return $value;
    }
    
    if (is_numeric($value)) {
        return $value > 0;
    }
    
    if (is_string($value)) {
        $value = strtolower($value);
        return in_array($value, ['true', 'yes', 'y', '1', 'on']);
    }
    
    return (bool)$value;
}

/**
 * Get the application environment
 * 
 * @return string Environment name
 */
function app_environment()
{
    return APP_ENV;
}

/**
 * Check if the application is in development mode
 * 
 * @return bool True if in development mode
 */
function is_development()
{
    return app_environment() === 'development';
}

/**
 * Check if the application is in production mode
 * 
 * @return bool True if in production mode
 */
function is_production()
{
    return app_environment() === 'production';
}

/**
 * Check if the application is in testing mode
 * 
 * @return bool True if in testing mode
 */
function is_testing()
{
    return app_environment() === 'testing';
}

/**
 * Get a configuration value
 * 
 * @param string $key Configuration key
 * @param mixed $default Default value
 * @return mixed Configuration value
 */
function config($key, $default = null)
{
    $parts = explode('.', $key);
    $file = array_shift($parts);
    
    // Load the configuration file if it exists
    $config_file = ROOT_PATH . '/config/' . $file . '.php';
    
    if (!file_exists($config_file)) {
        return $default;
    }
    
    // Include the configuration file
    $config = include $config_file;
    
    // Navigate through the configuration array
    foreach ($parts as $part) {
        if (!isset($config[$part])) {
            return $default;
        }
        
        $config = $config[$part];
    }
    
    return $config;
}
