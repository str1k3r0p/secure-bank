<?php
/**
 * Banking DVWA Project
 * Brute Force - High Security Implementation
 * 
 * This class implements the brute force vulnerability with high security measures.
 * It provides strong protection by using multiple defense mechanisms:
 * - Parameterized queries to prevent SQL injection
 * - Proper password hashing with bcrypt
 * - Exponential backoff for failed login attempts
 * - Account lockout after multiple failed attempts
 * - CAPTCHA requirement after a few failed attempts
 * - IP-based and username-based tracking
 */

namespace Vulnerabilities\bruteforce;

use App\Core\Database;
use App\Core\Security;
use App\Core\Logger;

class HighSecurity extends BruteForce
{
    /**
     * Initial maximum login attempts before CAPTCHA
     */
    const CAPTCHA_THRESHOLD = 3;
    
    /**
     * Maximum login attempts before temporary account lockout
     */
    const MAX_ATTEMPTS = 5;
    
    /**
     * Base lockout time in seconds (10 minutes)
     */
    const BASE_LOCKOUT_TIME = 600;
    
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
        if ($this->isIpLockedOut($ip)) {
            Logger::security("Brute force attempt blocked (HIGH) - IP is locked out", [
                'ip' => $ip,
                'username' => $username
            ]);
            
            $lockout_remaining = $this->getIpLockoutRemaining($ip);
            return [
                'error' => 'Too many failed login attempts. Please try again later.',
                'lockout_remaining' => $lockout_remaining
            ];
        }
        
        // Check if this username is locked out
        if ($this->isUsernameLockedOut($username)) {
            Logger::security("Brute force attempt blocked (HIGH) - Username is locked out", [
                'ip' => $ip,
                'username' => $username
            ]);
            
            $lockout_remaining = $this->getUsernameLockoutRemaining($username);
            return [
                'error' => 'Too many failed login attempts for this account. Please try again later.',
                'lockout_remaining' => $lockout_remaining
            ];
        }
        
        // Check if CAPTCHA is required
        $captcha_required = $this->isCaptchaRequired($ip, $username);
        if ($captcha_required && (!isset($input['captcha']) || !$this->verifyCaptcha($input['captcha']))) {
            Logger::security("Brute force attempt blocked (HIGH) - CAPTCHA failed or missing", [
                'ip' => $ip,
                'username' => $username
            ]);
            
            return [
                'error' => 'Please complete the CAPTCHA challenge.',
                'captcha_required' => true
            ];
        }
        
        // Initialize database
        $db = Database::getInstance();
        
        // SECURE: First get the user record to check password with proper hashing
        $query = "SELECT id, username, password, first_name, last_name, email, role, status 
                  FROM " . DB_PREFIX . "users 
                  WHERE (username = :username OR email = :username) 
                  AND status = 'active'";
        
        $params = ['username' => $username];
        
        // Execute query
        $user = $db->fetch($query, $params);
        
        // Check if user exists and password is correct
        if ($user && Security::verifyPassword($password, $user['password'])) {
            // Successful login - reset login attempts
            $this->resetLoginAttempts($ip, $username);
            
            Logger::security("Brute force login successful (HIGH)", [
                'username' => $username,
                'ip' => $ip
            ]);
            
            // Remove password from user data
            unset($user['password']);
            
            return $user;
        } else {
            // Failed login - increment login attempts
            $this->incrementLoginAttempts($ip, $username);
            
            $ip_attempts = $this->getIpLoginAttempts($ip);
            $username_attempts = $this->getUsernameLoginAttempts($username);
            
            Logger::security("Brute force login failed (HIGH)", [
                'username' => $username,
                'ip' => $ip,
                'ip_attempts' => $ip_attempts,
                'username_attempts' => $username_attempts
            ]);
            
            // Check if max attempts reached for IP
            if ($ip_attempts >= self::MAX_ATTEMPTS) {
                $this->lockOutIp($ip);
                return [
                    'error' => 'Too many failed login attempts. Your IP has been temporarily blocked.',
                    'lockout_remaining' => $this->getIpLockoutRemaining($ip)
                ];
            }
            
            // Check if max attempts reached for username
            if ($username_attempts >= self::MAX_ATTEMPTS) {
                $this->lockOutUsername($username);
                return [
                    'error' => 'Too many failed login attempts for this account. The account has been temporarily locked.',
                    'lockout_remaining' => $this->getUsernameLockoutRemaining($username)
                ];
            }
            
            // Check if CAPTCHA should be required
            if ($ip_attempts >= self::CAPTCHA_THRESHOLD || $username_attempts >= self::CAPTCHA_THRESHOLD) {
                return [
                    'error' => 'Login failed. Please complete the CAPTCHA challenge.',
                    'captcha_required' => true
                ];
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
    private function getIpLoginAttempts($ip)
    {
        if (!isset($_SESSION['ip_login_attempts'])) {
            $_SESSION['ip_login_attempts'] = [];
        }
        
        if (!isset($_SESSION['ip_login_attempts'][$ip])) {
            $_SESSION['ip_login_attempts'][$ip] = [
                'count' => 0,
                'first_attempt' => time(),
                'last_attempt' => time()
            ];
        }
        
        return $_SESSION['ip_login_attempts'][$ip]['count'];
    }
    
    /**
     * Get the number of login attempts for a username
     * 
     * @param string $username Username
     * @return int Number of attempts
     */
    private function getUsernameLoginAttempts($username)
    {
        if (!isset($_SESSION['username_login_attempts'])) {
            $_SESSION['username_login_attempts'] = [];
        }
        
        if (!isset($_SESSION['username_login_attempts'][$username])) {
            $_SESSION['username_login_attempts'][$username] = [
                'count' => 0,
                'first_attempt' => time(),
                'last_attempt' => time()
            ];
        }
        
        return $_SESSION['username_login_attempts'][$username]['count'];
    }
    
    /**
     * Increment login attempts
     * 
     * @param string $ip IP address
     * @param string $username Username
     */
    private function incrementLoginAttempts($ip, $username)
    {
        // Increment IP-based attempts
        if (!isset($_SESSION['ip_login_attempts'])) {
            $_SESSION['ip_login_attempts'] = [];
        }
        
        if (!isset($_SESSION['ip_login_attempts'][$ip])) {
            $_SESSION['ip_login_attempts'][$ip] = [
                'count' => 0,
                'first_attempt' => time(),
                'last_attempt' => time()
            ];
        }
        
        $_SESSION['ip_login_attempts'][$ip]['count']++;
        $_SESSION['ip_login_attempts'][$ip]['last_attempt'] = time();
        
        // Increment username-based attempts
        if (!isset($_SESSION['username_login_attempts'])) {
            $_SESSION['username_login_attempts'] = [];
        }
        
        if (!isset($_SESSION['username_login_attempts'][$username])) {
            $_SESSION['username_login_attempts'][$username] = [
                'count' => 0,
                'first_attempt' => time(),
                'last_attempt' => time()
            ];
        }
        
        $_SESSION['username_login_attempts'][$username]['count']++;
        $_SESSION['username_login_attempts'][$username]['last_attempt'] = time();
    }
    
    /**
     * Reset login attempts
     * 
     * @param string $ip IP address
     * @param string $username Username
     */
    private function resetLoginAttempts($ip, $username)
    {
        // Reset IP-based attempts
        if (isset($_SESSION['ip_login_attempts'][$ip])) {
            $_SESSION['ip_login_attempts'][$ip]['count'] = 0;
        }
        
        // Reset username-based attempts
        if (isset($_SESSION['username_login_attempts'][$username])) {
            $_SESSION['username_login_attempts'][$username]['count'] = 0;
        }
    }
    
    /**
     * Lock out an IP address with exponential backoff
     * 
     * @param string $ip IP address
     */
    private function lockOutIp($ip)
    {
        if (!isset($_SESSION['ip_lockouts'])) {
            $_SESSION['ip_lockouts'] = [];
        }
        
        // Get previous lockout count
        $lockout_count = isset($_SESSION['ip_lockouts'][$ip]['count']) ? $_SESSION['ip_lockouts'][$ip]['count'] : 0;
        
        // Calculate lockout time with exponential backoff
        $lockout_time = self::BASE_LOCKOUT_TIME * pow(2, $lockout_count);
        
        $_SESSION['ip_lockouts'][$ip] = [
            'expires' => time() + $lockout_time,
            'count' => $lockout_count + 1
        ];
        
        Logger::security("IP locked out due to brute force attempts (HIGH)", [
            'ip' => $ip,
            'lockout_count' => $lockout_count + 1,
            'lockout_duration' => $lockout_time,
            'lockout_expires' => date('Y-m-d H:i:s', time() + $lockout_time)
        ]);
    }
    
    /**
     * Lock out a username with exponential backoff
     * 
     * @param string $username Username
     */
    private function lockOutUsername($username)
    {
        if (!isset($_SESSION['username_lockouts'])) {
            $_SESSION['username_lockouts'] = [];
        }
        
        // Get previous lockout count
        $lockout_count = isset($_SESSION['username_lockouts'][$username]['count']) ? $_SESSION['username_lockouts'][$username]['count'] : 0;
        
        // Calculate lockout time with exponential backoff
        $lockout_time = self::BASE_LOCKOUT_TIME * pow(2, $lockout_count);
        
        $_SESSION['username_lockouts'][$username] = [
            'expires' => time() + $lockout_time,
            'count' => $lockout_count + 1
        ];
        
        Logger::security("Username locked out due to brute force attempts (HIGH)", [
            'username' => $username,
            'lockout_count' => $lockout_count + 1,
            'lockout_duration' => $lockout_time,
            'lockout_expires' => date('Y-m-d H:i:s', time() + $lockout_time)
        ]);
    }
    
    /**
     * Check if an IP is locked out
     * 
     * @param string $ip IP address
     * @return bool True if locked out
     */
    private function isIpLockedOut($ip)
    {
        if (!isset($_SESSION['ip_lockouts']) || !isset($_SESSION['ip_lockouts'][$ip])) {
            return false;
        }
        
        $lockout_expires = $_SESSION['ip_lockouts'][$ip]['expires'];
        
        if (time() >= $lockout_expires) {
            // Lockout expired but keep the count
            $_SESSION['ip_lockouts'][$ip]['expires'] = 0;
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if a username is locked out
     * 
     * @param string $username Username
     * @return bool True if locked out
     */
    private function isUsernameLockedOut($username)
    {
        if (!isset($_SESSION['username_lockouts']) || !isset($_SESSION['username_lockouts'][$username])) {
            return false;
        }
        
        $lockout_expires = $_SESSION['username_lockouts'][$username]['expires'];
        
        if (time() >= $lockout_expires) {
            // Lockout expired but keep the count
            $_SESSION['username_lockouts'][$username]['expires'] = 0;
            return false;
        }
        
        return true;
    }
    
    /**
     * Get remaining lockout time for an IP
     * 
     * @param string $ip IP address
     * @return int Remaining lockout time in seconds
     */
    private function getIpLockoutRemaining($ip)
    {
        if (!isset($_SESSION['ip_lockouts']) || !isset($_SESSION['ip_lockouts'][$ip])) {
            return 0;
        }
        
        $lockout_expires = $_SESSION['ip_lockouts'][$ip]['expires'];
        $remaining = $lockout_expires - time();
        
        return max(0, $remaining);
    }
    
    /**
     * Get remaining lockout time for a username
     * 
     * @param string $username Username
     * @return int Remaining lockout time in seconds
     */
    private function getUsernameLockoutRemaining($username)
    {
        if (!isset($_SESSION['username_lockouts']) || !isset($_SESSION['username_lockouts'][$username])) {
            return 0;
        }
        
        $lockout_expires = $_SESSION['username_lockouts'][$username]['expires'];
        $remaining = $lockout_expires - time();
        
        return max(0, $remaining);
    }
    
    /**
     * Check if CAPTCHA is required
     * 
     * @param string $ip IP address
     * @param string $username Username
     * @return bool True if CAPTCHA is required
     */
    private function isCaptchaRequired($ip, $username)
    {
        $ip_attempts = $this->getIpLoginAttempts($ip);
        $username_attempts = $this->getUsernameLoginAttempts($username);
        
        return ($ip_attempts >= self::CAPTCHA_THRESHOLD || $username_attempts >= self::CAPTCHA_THRESHOLD);
    }
    
    /**
     * Verify CAPTCHA
     * 
     * @param string $captcha CAPTCHA response
     * @return bool True if CAPTCHA is valid
     */
    private function verifyCaptcha($captcha)
    {
        // In a real application, this would verify the CAPTCHA with a service
        // For demonstration purposes, we'll just check if it's not empty
        return !empty($captcha);
    }
}