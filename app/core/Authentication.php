<?php
/**
 * Banking DVWA Project
 * Authentication Class
 * 
 * This class handles user authentication.
 */

namespace App\Core;

class Authentication
{
    /**
     * @var int The number of failed login attempts
     */
    private static $failedAttempts = 0;
    
    /**
     * Initialize authentication
     */
    public static function init()
    {
        // Initialize failed attempts counter from session
        if (isset($_SESSION['failed_attempts'])) {
            self::$failedAttempts = $_SESSION['failed_attempts'];
        }
    }
    
    /**
     * Authenticate a user
     * 
     * @param string $username The username
     * @param string $password The password
     * @param string $securityLevel The security level for brute force protection
     * @return bool True if authentication succeeded, false otherwise
     */
    public static function login($username, $password, $securityLevel = null)
    {
        // Get brute force security level if not provided
        if ($securityLevel === null) {
            $securityLevel = Security::getSecurityLevel('brute_force');
        }
        
        // Check for brute force protection based on security level
        if (self::isBruteForceDetected($securityLevel)) {
            // Log brute force attempt
            Logger::warning("Brute force attempt detected", [
                'username' => $username,
                'ip' => get_client_ip(),
                'security_level' => $securityLevel
            ]);
            
            return false;
        }
        
        // Get user model
        $user_model = new \App\Models\User();
        
        // Prepare authentication based on security level
        switch ($securityLevel) {
            case 'low':
                // Low security - vulnerable to SQL injection
                $query = "SELECT * FROM " . DB_PREFIX . "users WHERE username = '{$username}' AND password = '{$password}' LIMIT 1";
                $user = $user_model->query($query, [], false);
                break;
                
            case 'medium':
                // Medium security - SQL injection protected but uses direct password comparison
                $query = "SELECT * FROM " . DB_PREFIX . "users WHERE username = :username AND password = :password LIMIT 1";
                $params = [
                    'username' => $username,
                    'password' => $password
                ];
                $user = $user_model->query($query, $params, false);
                break;
                
            case 'high':
            default:
                // High security - SQL injection protected and uses password hashing
                $user = $user_model->findOneBy('username', $username);
                if (!$user || !Security::verifyPassword($password, $user['password'])) {
                    $user = null;
                }
                break;
        }
        
        // If authentication failed
        if (!$user) {
            // Increment failed attempts
            self::incrementFailedAttempts();
            
            // Log failed login
            Logger::warning("Failed login attempt", [
                'username' => $username,
                'ip' => get_client_ip(),
                'security_level' => $securityLevel
            ]);
            
            return false;
        }
        
        // Reset failed attempts
        self::resetFailedAttempts();
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['auth_time'] = time();
        
        // Log successful login
        Logger::info("User logged in", [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'ip' => get_client_ip()
        ]);
        
        return true;
    }
    
    /**
     * Log out the current user
     */
    public static function logout()
    {
        // Log logout
        if (isset($_SESSION['user_id'])) {
            Logger::info("User logged out", [
                'user_id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'] ?? 'unknown',
                'ip' => get_client_ip()
            ]);
        }
        
        // Unset session variables
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        unset($_SESSION['user_role']);
        unset($_SESSION['auth_time']);
        
        // Destroy the session
        session_destroy();
    }
    
    /**
     * Check if a user is logged in
     * 
     * @return bool True if the user is logged in, false otherwise
     */
    public static function isLoggedIn()
    {
        // Check if user ID is set in session
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            return false;
        }
        
        // Check if authentication has expired
        if (isset($_SESSION['auth_time'])) {
            $auth_time = $_SESSION['auth_time'];
            $current_time = time();
            
            if ($current_time - $auth_time > SESSION_EXPIRATION) {
                // Log session expiration
                Logger::info("Session expired", [
                    'user_id' => $_SESSION['user_id'],
                    'username' => $_SESSION['username'] ?? 'unknown',
                    'ip' => get_client_ip()
                ]);
                
                // Force logout
                self::logout();
                return false;
            }
            
            // Update auth time
            $_SESSION['auth_time'] = $current_time;
        }
        
        return true;
    }
    
    /**
     * Check if the current user has a specific role
     * 
     * @param string $role The role to check
     * @return bool True if the user has the role, false otherwise
     */
    public static function hasRole($role)
    {
        return self::isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
    }
    
    /**
     * Get the current user ID
     * 
     * @return int|null The user ID or null if not logged in
     */
    public static function getUserId()
    {
        return self::isLoggedIn() ? $_SESSION['user_id'] : null;
    }
    
    /**
     * Get the current username
     * 
     * @return string|null The username or null if not logged in
     */
    public static function getUsername()
    {
        return self::isLoggedIn() ? $_SESSION['username'] : null;
    }
    
    /**
     * Get the current user role
     * 
     * @return string|null The user role or null if not logged in
     */
    public static function getUserRole()
    {
        return self::isLoggedIn() ? $_SESSION['user_role'] : null;
    }
    
    /**
     * Increment the failed login attempts counter
     */
    private static function incrementFailedAttempts()
    {
        self::$failedAttempts++;
        $_SESSION['failed_attempts'] = self::$failedAttempts;
    }
    
    /**
     * Reset the failed login attempts counter
     */
    private static function resetFailedAttempts()
    {
        self::$failedAttempts = 0;
        $_SESSION['failed_attempts'] = 0;
    }
    
    /**
     * Check if brute force is detected based on security level
     * 
     * @param string $securityLevel The security level
     * @return bool True if brute force is detected, false otherwise
     */
    private static function isBruteForceDetected($securityLevel)
    {
        switch ($securityLevel) {
            case 'low':
                // Low security - no brute force protection
                return false;
                
            case 'medium':
                // Medium security - basic brute force protection
                // Lock after 5 failed attempts for 1 minute
                if (self::$failedAttempts >= 5) {
                    // Check if lock time has expired
                    if (isset($_SESSION['lock_until']) && time() < $_SESSION['lock_until']) {
                        return true;
                    }
                    
                    // Set lock time
                    $_SESSION['lock_until'] = time() + 60;
                    
                    // Reset failed attempts
                    self::resetFailedAttempts();
                    
                    return true;
                }
                return false;
                
            case 'high':
            default:
                // High security - advanced brute force protection
                // Lock after 3 failed attempts for progressively longer periods
                if (self::$failedAttempts >= 3) {
                    // Calculate lock time based on number of failed attempts
                    $lockTime = pow(2, self::$failedAttempts - 3) * 60; // 1, 2, 4, 8, 16... minutes
                    
                    // Check if lock time has expired
                    if (isset($_SESSION['lock_until']) && time() < $_SESSION['lock_until']) {
                        return true;
                    }
                    
                    // Set lock time
                    $_SESSION['lock_until'] = time() + $lockTime;
                    
                    return true;
                }
                return false;
        }
    }
    
    /**
     * Get the remaining lock time in seconds
     * 
     * @return int The remaining lock time or 0 if not locked
     */
    public static function getLockTime()
    {
        if (isset($_SESSION['lock_until'])) {
            $remaining = $_SESSION['lock_until'] - time();
            
            if ($remaining > 0) {
                return $remaining;
            }
        }
        
        return 0;
    }
}