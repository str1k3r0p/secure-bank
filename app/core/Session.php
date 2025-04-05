<?php
/**
 * Banking DVWA Project
 * Session Class
 * 
 * This class handles session management.
 */

namespace App\Core;

class Session
{
    /**
     * Initialize the session
     */
    public static function init()
    {
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
        }
        
        // Regenerate session ID if needed
        self::regenerateIdIfNeeded();
    }
    
    /**
     * Regenerate session ID if needed
     */
    private static function regenerateIdIfNeeded()
    {
        if (SESSION_REGENERATE_ID) {
            if (!isset($_SESSION['last_regeneration'])) {
                self::regenerateId();
            } elseif (time() - $_SESSION['last_regeneration'] > 300) {
                self::regenerateId();
            }
        }
    }
    
    /**
     * Regenerate session ID
     */
    public static function regenerateId()
    {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
    
    /**
     * Set a session value
     * 
     * @param string $key The key
     * @param mixed $value The value
     */
    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }
    
    /**
     * Get a session value
     * 
     * @param string $key The key
     * @param mixed $default The default value if the key doesn't exist
     * @return mixed The session value
     */
    public static function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Check if a session key exists
     * 
     * @param string $key The key
     * @return bool True if the key exists, false otherwise
     */
    public static function has($key)
    {
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove a session value
     * 
     * @param string $key The key
     */
    public static function remove($key)
    {
        unset($_SESSION[$key]);
    }
    
    /**
     * Clear all session data
     */
    public static function clear()
    {
        $_SESSION = [];
    }
    
    /**
     * Destroy the session
     */
    public static function destroy()
    {
        // Clear session data
        self::clear();
        
        // Delete the session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        // Destroy the session
        session_destroy();
    }
    
    /**
     * Set a flash message
     * 
     * @param string $type The message type (success, error, info, warning)
     * @param string $message The message to display
     */
    public static function setFlash($type, $message)
    {
        $_SESSION['flash_message'] = [
            'type' => $type,
            'message' => $message
        ];
    }
    
    /**
     * Get flash message
     * 
     * @return array|null The flash message or null if none exists
     */
    public static function getFlash()
    {
        if (isset($_SESSION['flash_message'])) {
            $flash_message = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
            return $flash_message;
        }
        return null;
    }
    
    /**
     * Check if a flash message exists
     * 
     * @return bool True if a flash message exists, false otherwise
     */
    public static function hasFlash()
    {
        return isset($_SESSION['flash_message']);
    }
    
    /**
     * Set user data
     * 
     * @param array $user The user data
     */
    public static function setUser($user)
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['auth_time'] = time();
    }
    
    /**
     * Get user data
     * 
     * @return array|null The user data or null if not logged in
     */
    public static function getUser()
    {
        if (self::isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'role' => $_SESSION['user_role']
            ];
        }
        return null;
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
     * Log out the current user
     */
    public static function logout()
    {
        // Unset session variables
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        unset($_SESSION['user_role']);
        unset($_SESSION['auth_time']);
        
        // Regenerate session ID
        self::regenerateId();
    }
}