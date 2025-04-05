<?php
/**
 * Banking DVWA Project
 * Brute Force - Medium Security Implementation
 * 
 * This class implements the brute force vulnerability with medium security measures.
 * It provides basic protection by implementing a simple login attempt counter and
 * temporary lockout after multiple failed attempts.
 */

namespace Vulnerabilities\bruteforce;

use App\Core\Database;
use App\Core\Logger;

class MediumSecurity extends BruteForce
{
    /**
     * Maximum number of login attempts before lockout
     */
    const MAX_ATTEMPTS = 5;
    
    /**
     * Lockout time in seconds (5 minutes)
     */
    const LOCKOUT_TIME = 300;
    
    /**
     * Execute the vulnerability
     * 
     * @param array $input Input data
     * @return array|bool Authentication result or false if failed
     */
    public function execute($input)
    {
        // Check if username and password are provided
        if (!isset($input['username']) || !isset($input['password'])) {
            return false;
        }
        
        // Get credentials from input
        $username = $input['username'];
        $password = $input['password'];
        
        // Get client IP for tracking login attempts
        $ip = get_client_ip();
        
        // Check if this IP is locked out
        if ($this->isLockedOut($ip)) {
            Logger::security("Brute force attempt blocked (MEDIUM) - IP is locked out", [
                'ip' => $ip,
                'username' => $username
            ]);
            return ['error' => 'Too many failed login attempts. Please try again later.'];
        }
        
        // Initialize database
        $db = Database::getInstance();
        
        // SOMEWHAT SECURE: Using parameterized query (prevents SQL injection)
        // Still vulnerable to brute force but with rate limiting
        $query = "SELECT id, username, first_name, last_name, email, role, status 
                  FROM " . DB_PREFIX . "users 
                  WHERE (username = :username OR email = :username) 
                  AND password = :password 
                  AND status = 'active'";
        
        $params = [
            'username' => $username,
            'password' => $password
        ];
        
        // Execute query
        $user = $db->fetch($query, $params);
        
        // Handle the result
        if ($user) {
            // Successful login - reset login attempts
            $this->resetLoginAttempts($ip);
            
            Logger::security("Brute force login successful (MEDIUM)", [
                'username' => $username,
                'ip' => $ip
            ]);
            
            return $user;
        } else {
            // Failed login - increment login attempts
            $this->incrementLoginAttempts($ip);
            
            Logger::security("Brute force login failed (MEDIUM)", [
                'username' => $username,
                'ip' => $ip,
                'attempts' => $this->getLoginAttempts($ip)
            ]);
            
            // Check if max attempts reached
            if ($this->getLoginAttempts($ip) >= self::MAX_ATTEMPTS) {
                $this->lockOut($ip);
                return ['error' => 'Too many failed login attempts. Your IP has been temporarily blocked.'];
            }
            
            return false;
        }
    }
    
    /**
     * Get the number of login attempts for an IP
     * 
     * @param string $ip IP address
     * @return int Number of attempts
     */
    private function getLoginAttempts($ip)
    {
        // Using session to track login attempts (for demonstration purposes)
        // In a real application, this would be stored in a database
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = [];
        }
        
        if (!isset($_SESSION['login_attempts'][$ip])) {
            $_SESSION['login_attempts'][$ip] = 0;
        }
        
        return $_SESSION['login_attempts'][$ip];
    }
    
    /**
     * Increment login attempts for an IP
     * 
     * @param string $ip IP address
     */
    private function incrementLoginAttempts($ip)
    {
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = [];
        }
        
        if (!isset($_SESSION['login_attempts'][$ip])) {
            $_SESSION['login_attempts'][$ip] = 0;
        }
        
        $_SESSION['login_attempts'][$ip]++;
    }
    
    /**
     * Reset login attempts for an IP
     * 
     * @param string $ip IP address
     */
    private function resetLoginAttempts($ip)
    {
        if (isset($_SESSION['login_attempts'][$ip])) {
            $_SESSION['login_attempts'][$ip] = 0;
        }
    }
    
    /**
     * Lock out an IP address
     * 
     * @param string $ip IP address
     */
    private function lockOut($ip)
    {
        if (!isset($_SESSION['lockouts'])) {
            $_SESSION['lockouts'] = [];
        }
        
        $_SESSION['lockouts'][$ip] = time() + self::LOCKOUT_TIME;
        
        Logger::security("IP locked out due to brute force attempts (MEDIUM)", [
            'ip' => $ip,
            'lockout_expires' => date('Y-m-d H:i:s', $_SESSION['lockouts'][$ip])
        ]);
    }
    
    /**
     * Check if an IP is locked out
     * 
     * @param string $ip IP address
     * @return bool True if locked out
     */
    private function isLockedOut($ip)
    {
        if (!isset($_SESSION['lockouts']) || !isset($_SESSION['lockouts'][$ip])) {
            return false;
        }
        
        $lockout_time = $_SESSION['lockouts'][$ip];
        
        if (time() >= $lockout_time) {
            // Lockout expired
            unset($_SESSION['lockouts'][$ip]);
            return false;
        }
        
        return true;
    }
}