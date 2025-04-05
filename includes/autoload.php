<?php
/**
 * Banking DVWA Project
 * Class Autoloader
 * 
 * This file automatically loads classes when they are instantiated.
 */

// Prevent direct access to this file
if (!defined('ROOT_PATH')) {
    die('Direct access to this file is not allowed.');
}

/**
 * Autoload function for classes
 * 
 * @param string $class_name The name of the class to load
 */
function autoload($class_name) {
    // Convert namespace separators to directory separators
    $class_name = str_replace('\\', '/', $class_name);
    
    // Map of namespace prefixes to directories
    $namespaces = [
        'App/Core' => APP_PATH . '/core',
        'App/Controllers' => APP_PATH . '/controllers',
        'App/Models' => APP_PATH . '/models',
        'App/Helpers' => APP_PATH . '/helpers',
        'Vulnerabilities' => VULNERABILITY_PATH
    ];
    
    // Check each namespace prefix
    foreach ($namespaces as $prefix => $dir) {
        // If the class uses the namespace prefix
        if (strpos($class_name, $prefix) === 0) {
            // Get the relative class name
            $relative_class = substr($class_name, strlen($prefix) + 1);
            
            // Replace namespace separator with directory separator
            $file = $dir . '/' . $relative_class . '.php';
            
            // If the file exists, require it
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
    
    // If no namespace matches, try a direct file path
    $file = ROOT_PATH . '/' . $class_name . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
}

// Register the autoload function
spl_autoload_register('autoload');