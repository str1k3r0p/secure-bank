<?php
/**
 * Banking DVWA Project
 * Error Controller
 * 
 * Handles error pages and error responses.
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Logger;

class ErrorController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * 404 Not Found error
     */
    public function notFound()
    {
        // Set HTTP status code
        http_response_code(404);
        
        // Log error
        Logger::warning("404 Not Found", [
            'uri' => $_SERVER['REQUEST_URI'],
            'ip' => get_client_ip(),
            'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'guest'
        ]);
        
        // Render error page
        $this->render('errors/404', [
            'title' => '404 Not Found',
            'message' => 'The page you are looking for does not exist or has been moved.'
        ]);
    }
    
    /**
     * 500 Internal Server Error
     */
    public function serverError()
    {
        // Set HTTP status code
        http_response_code(500);
        
        // Log error
        Logger::error("500 Internal Server Error", [
            'uri' => $_SERVER['REQUEST_URI'],
            'ip' => get_client_ip(),
            'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'guest'
        ]);
        
        // Render error page
        $this->render('errors/500', [
            'title' => '500 Internal Server Error',
            'message' => 'The server encountered an internal error and was unable to complete your request.'
        ]);
    }
    
    /**
     * General error
     */
    public function general()
    {
        // Set HTTP status code
        http_response_code(400);
        
        // Get error message
        $message = isset($_GET['message']) ? $_GET['message'] : 'An error has occurred.';
        
        // Log error
        Logger::warning("General Error", [
            'message' => $message,
            'uri' => $_SERVER['REQUEST_URI'],
            'ip' => get_client_ip(),
            'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'guest'
        ]);
        
        // Render error page
        $this->render('errors/general', [
            'title' => 'Error',
            'message' => $message
        ]);
    }
    
    /**
     * Access denied error
     */
    public function forbidden()
    {
        // Set HTTP status code
        http_response_code(403);
        
        // Log error
        Logger::warning("403 Forbidden", [
            'uri' => $_SERVER['REQUEST_URI'],
            'ip' => get_client_ip(),
            'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'guest'
        ]);
        
        // Render error page
        $this->render('errors/403', [
            'title' => '403 Forbidden',
            'message' => 'You do not have permission to access this resource.'
        ]);
    }
    
    /**
     * Maintenance mode error
     */
    public function maintenance()
    {
        // Set HTTP status code
        http_response_code(503);
        
        // Log access
        Logger::info("Maintenance page accessed", [
            'ip' => get_client_ip(),
            'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'guest'
        ]);
        
        // Render maintenance page
        $this->render('errors/maintenance', [
            'title' => 'Maintenance Mode',
            'message' => 'The site is currently under maintenance. Please check back later.'
        ], 'minimal'); // Use minimal layout without navigation
    }
}