<?php
/**
 * Banking DVWA Project
 * Home Controller
 * 
 * This controller handles the home page and about page.
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Logger;

class HomeController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Home page
     */
    public function index()
    {
        // Log page access
        Logger::access("Home page accessed", [
            'user_id' => $this->isLoggedIn() ? $_SESSION['user_id'] : 'guest',
            'ip' => get_client_ip()
        ]);
        
        // Render home page
        $this->render('home/index', [
            'title' => 'Welcome to ' . APP_NAME,
            'isLoggedIn' => $this->isLoggedIn()
        ]);
    }
    
    /**
     * About page
     */
    public function about()
    {
        // Log page access
        Logger::access("About page accessed", [
            'user_id' => $this->isLoggedIn() ? $_SESSION['user_id'] : 'guest',
            'ip' => get_client_ip()
        ]);
        
        // Render about page
        $this->render('home/about', [
            'title' => 'About ' . APP_NAME,
            'isLoggedIn' => $this->isLoggedIn()
        ]);
    }
}