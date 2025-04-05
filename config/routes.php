<?php
/**
 * Banking DVWA Project
 * Routing Configuration
 * 
 * This file defines the routes for the application.
 */

// Prevent direct access to this file
if (!defined('ROOT_PATH')) {
    die('Direct access to this file is not allowed.');
}

// Define routes as an associative array: 'url_pattern' => ['controller' => 'ControllerName', 'action' => 'actionMethod']
$routes = [
    // Home routes
    '/' => ['controller' => 'HomeController', 'action' => 'index'],
    '/home' => ['controller' => 'HomeController', 'action' => 'index'],
    '/about' => ['controller' => 'HomeController', 'action' => 'about'],
    
    // Authentication routes
    '/login' => ['controller' => 'AuthController', 'action' => 'login'],
    '/register' => ['controller' => 'AuthController', 'action' => 'register'],
    '/logout' => ['controller' => 'AuthController', 'action' => 'logout'],
    '/admin/login' => ['controller' => 'AuthController', 'action' => 'adminLogin'],
    
    // Account routes
    '/account' => ['controller' => 'AccountController', 'action' => 'dashboard'],
    '/account/dashboard' => ['controller' => 'AccountController', 'action' => 'dashboard'],
    '/account/details' => ['controller' => 'AccountController', 'action' => 'details'],
    '/account/statement' => ['controller' => 'AccountController', 'action' => 'statement'],
    '/account/settings' => ['controller' => 'AccountController', 'action' => 'settings'],
    
    // Transaction routes
    '/transaction/new' => ['controller' => 'TransactionController', 'action' => 'new'],
    '/transaction/history' => ['controller' => 'TransactionController', 'action' => 'history'],
    '/transaction/confirm' => ['controller' => 'TransactionController', 'action' => 'confirm'],
    '/transaction/process' => ['controller' => 'TransactionController', 'action' => 'process'],
    
    // Admin routes
    '/admin' => ['controller' => 'AdminController', 'action' => 'dashboard'],
    '/admin/dashboard' => ['controller' => 'AdminController', 'action' => 'dashboard'],
    '/admin/users' => ['controller' => 'AdminController', 'action' => 'users'],
    '/admin/transactions' => ['controller' => 'AdminController', 'action' => 'transactions'],
    '/admin/settings' => ['controller' => 'AdminController', 'action' => 'settings'],
    
    // Vulnerability routes
    '/vulnerabilities' => ['controller' => 'VulnerabilityController', 'action' => 'index'],
    '/vulnerabilities/brute-force' => ['controller' => 'VulnerabilityController', 'action' => 'bruteForce'],
    '/vulnerabilities/cmd-injection' => ['controller' => 'VulnerabilityController', 'action' => 'cmdInjection'],
    '/vulnerabilities/sql-injection' => ['controller' => 'VulnerabilityController', 'action' => 'sqlInjection'],
    '/vulnerabilities/directory-traversal' => ['controller' => 'VulnerabilityController', 'action' => 'directoryTraversal'],
    '/vulnerabilities/xss' => ['controller' => 'VulnerabilityController', 'action' => 'xss'],
    '/vulnerabilities/source' => ['controller' => 'VulnerabilityController', 'action' => 'source'],
    
    // Error routes
    '/error/404' => ['controller' => 'ErrorController', 'action' => 'notFound'],
    '/error/500' => ['controller' => 'ErrorController', 'action' => 'serverError'],
    '/error' => ['controller' => 'ErrorController', 'action' => 'general']
];

// Define the routes as a constant for use in the Router class
define('APP_ROUTES', $routes);