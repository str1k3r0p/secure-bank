<?php
/**
 * Banking DVWA Project
 * Authentication Controller
 * 
 * This controller handles user authentication.
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Authentication;
use App\Core\Security;
use App\Core\Logger;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * @var User The user model
     */
    private $userModel;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }
    
    /**
     * User login page
     */
    public function login()
    {
        // If already logged in, redirect to account dashboard
        if ($this->isLoggedIn()) {
            $this->redirect('/account/dashboard');
        }
        
        // Get security level for brute force vulnerability
        $security_level = Security::getSecurityLevel('brute_force');
        
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $this->input('username');
            $password = $this->input('password');
            
            // Check for brute force lock
            $lock_time = Authentication::getLockTime();
            if ($lock_time > 0 && $security_level !== 'low') {
                $minutes = ceil($lock_time / 60);
                $this->setFlash('error', "Too many failed login attempts. Please try again in {$minutes} minutes.");
                $this->render('auth/login', [
                    'username' => $username,
                    'lock_time' => $lock_time
                ]);
                return;
            }
            
            // Authenticate user with the current security level
            if (Authentication::login($username, $password, $security_level)) {
                // Log successful login
                Logger::info("User logged in successfully", [
                    'username' => $username,
                    'ip' => get_client_ip()
                ]);
                
                // Redirect to intended page or dashboard
                $return_url = $this->input('return', '/account/dashboard');
                $this->redirect($return_url);
            } else {
                // Log failed login
                Logger::warning("Failed login attempt", [
                    'username' => $username,
                    'ip' => get_client_ip(),
                    'security_level' => $security_level
                ]);
                
                $this->setFlash('error', 'Invalid username or password');
                $this->render('auth/login', [
                    'username' => $username
                ]);
            }
        } else {
            // Display login form
            $this->render('auth/login');
        }
    }
    
    /**
     * User registration page
     */
    public function register()
    {
        // If already logged in, redirect to account dashboard
        if ($this->isLoggedIn()) {
            $this->redirect('/account/dashboard');
        }
        
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get form data
            $username = $this->input('username');
            $email = $this->input('email');
            $password = $this->input('password');
            $password_confirm = $this->input('password_confirm');
            $first_name = $this->input('first_name');
            $last_name = $this->input('last_name');
            
            // Validate form data
            $errors = $this->validate([
                'username' => 'required|alpha_num|min:3|max:50',
                'email' => 'required|email',
                'password' => 'required|min:8',
                'password_confirm' => 'required|same:password',
                'first_name' => 'required|alpha|max:50',
                'last_name' => 'required|alpha|max:50'
            ]);
            
            // Check if username or email already exists
            if (empty($errors)) {
                if ($this->userModel->usernameExists($username)) {
                    $errors['username'] = 'Username already exists';
                }
                
                if ($this->userModel->emailExists($email)) {
                    $errors['email'] = 'Email already exists';
                }
            }
            
            if (empty($errors)) {
                // Create user
                $user_id = $this->userModel->create([
                    'username' => $username,
                    'email' => $email,
                    'password' => $password,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'role' => ROLE_USER,
                    'status' => 'active'
                ]);
                
                if ($user_id) {
                    // Log user creation
                    Logger::info("User registered successfully", [
                        'user_id' => $user_id,
                        'username' => $username,
                        'ip' => get_client_ip()
                    ]);
                    
                    $this->setFlash('success', 'Registration successful! You can now log in.');
                    $this->redirect('/login');
                } else {
                    $this->setFlash('error', 'Failed to create user');
                    $this->render('auth/register', [
                        'username' => $username,
                        'email' => $email,
                        'first_name' => $first_name,
                        'last_name' => $last_name
                    ]);
                }
            } else {
                // Display form with errors
                $this->render('auth/register', [
                    'username' => $username,
                    'email' => $email,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'errors' => $errors
                ]);
            }
        } else {
            // Display registration form
            $this->render('auth/register');
        }
    }
    
    /**
     * User logout
     */
    public function logout()
    {
        // Log the logout
        if ($this->isLoggedIn()) {
            Logger::info("User logged out", [
                'user_id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'ip' => get_client_ip()
            ]);
        }
        
        // Logout user
        Authentication::logout();
        
        $this->setFlash('success', 'You have been logged out');
        $this->redirect('/login');
    }
    
    /**
     * Admin login page
     */
    public function adminLogin()
    {
        // If already logged in as admin, redirect to admin dashboard
        if ($this->isLoggedIn() && $this->hasRole(ROLE_ADMIN)) {
            $this->redirect('/admin/dashboard');
        }
        
        // Get security level for brute force vulnerability
        $security_level = Security::getSecurityLevel('brute_force');
        
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $this->input('username');
            $password = $this->input('password');
            
            // Check for brute force lock
            $lock_time = Authentication::getLockTime();
            if ($lock_time > 0 && $security_level !== 'low') {
                $minutes = ceil($lock_time / 60);
                $this->setFlash('error', "Too many failed login attempts. Please try again in {$minutes} minutes.");
                $this->render('auth/admin_login', [
                    'username' => $username,
                    'lock_time' => $lock_time
                ]);
                return;
            }
            
            // Authenticate user
            if (Authentication::login($username, $password, $security_level)) {
                // Check if user is admin
                if ($this->hasRole(ROLE_ADMIN)) {
                    // Log successful admin login
                    Logger::info("Admin logged in successfully", [
                        'username' => $username,
                        'ip' => get_client_ip()
                    ]);
                    
                    $this->redirect('/admin/dashboard');
                } else {
                    // Log unauthorized access attempt
                    Logger::warning("Unauthorized admin access attempt", [
                        'username' => $username,
                        'ip' => get_client_ip()
                    ]);
                    
                    // Logout non-admin user
                    Authentication::logout();
                    
                    $this->setFlash('error', 'You do not have permission to access the admin area');
                    $this->render('auth/admin_login', [
                        'username' => $username
                    ]);
                }
            } else {
                // Log failed login
                Logger::warning("Failed admin login attempt", [
                    'username' => $username,
                    'ip' => get_client_ip(),
                    'security_level' => $security_level
                ]);
                
                $this->setFlash('error', 'Invalid username or password');
                $this->render('auth/admin_login', [
                    'username' => $username
                ]);
            }
        } else {
            // Display login form
            $this->render('auth/admin_login');
        }
    }
}