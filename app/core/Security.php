<?php
/**
 * Banking DVWA Project
 * Security Class
 * 
 * This class provides security-related utilities.
 */

namespace App\Core;

class Security
{
    /**
     * @var array Security level handlers for each vulnerability
     */
    private static $securityLevels = [];
    
    /**
     * Initialize security levels
     */
    public static function init()
    {
        // Initialize security levels for each vulnerability
        self::$securityLevels = [
            'brute_force' => DEFAULT_SECURITY_LEVELS['brute_force'],
            'cmd_injection' => DEFAULT_SECURITY_LEVELS['cmd_injection'],
            'sql_injection' => DEFAULT_SECURITY_LEVELS['sql_injection'],
            'directory_traversal' => DEFAULT_SECURITY_LEVELS['directory_traversal'],
            'xss' => DEFAULT_SECURITY_LEVELS['xss']
        ];
        
        // Load security levels from session if available
        if (isset($_SESSION['security_levels']) && is_array($_SESSION['security_levels'])) {
            self::$securityLevels = array_merge(self::$securityLevels, $_SESSION['security_levels']);
        }
    }
    
    /**
     * Get security level for a vulnerability
     * 
     * @param string $vulnerability The vulnerability name
     * @return string The security level
     */
    public static function getSecurityLevel($vulnerability)
    {
        if (!isset(self::$securityLevels[$vulnerability])) {
            return DEFAULT_SECURITY_LEVEL;
        }
        
        return self::$securityLevels[$vulnerability];
    }
    
    /**
     * Set security level for a vulnerability
     * 
     * @param string $vulnerability The vulnerability name
     * @param string $level The security level
     */
    public static function setSecurityLevel($vulnerability, $level)
    {
        // Validate security level
        if (!in_array($level, SECURITY_LEVELS)) {
            throw new \InvalidArgumentException("Invalid security level: {$level}");
        }
        
        // Set security level
        self::$securityLevels[$vulnerability] = $level;
        
        // Save to session
        $_SESSION['security_levels'][$vulnerability] = $level;
        
        // Log security level change
        Logger::info("Security level changed: {$vulnerability} => {$level}", [
            'user_id' => $_SESSION['user_id'] ?? 'guest',
            'ip' => get_client_ip()
        ]);
    }
    
    /**
     * Reset security levels to default
     */
    public static function resetSecurityLevels()
    {
        self::$securityLevels = DEFAULT_SECURITY_LEVELS;
        $_SESSION['security_levels'] = DEFAULT_SECURITY_LEVELS;
        
        // Log security level reset
        Logger::info("Security levels reset to default", [
            'user_id' => $_SESSION['user_id'] ?? 'guest',
            'ip' => get_client_ip()
        ]);
    }
    
    /**
     * Get all security levels
     * 
     * @return array The security levels
     */
    public static function getAllSecurityLevels()
    {
        return self::$securityLevels;
    }
    
    /**
     * Generate a secure random token
     * 
     * @param int $length The token length
     * @return string The random token
     */
    public static function generateToken($length = 32)
    {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Hash a password
     * 
     * @param string $password The password to hash
     * @return string The hashed password
     */
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_HASH_ALGO, [
            'cost' => PASSWORD_HASH_COST
        ]);
    }
    
    /**
     * Verify a password against a hash
     * 
     * @param string $password The password to verify
     * @param string $hash The hash to verify against
     * @return bool True if the password is valid, false otherwise
     */
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
    
    /**
     * Sanitize user input
     * 
     * @param mixed $input The input to sanitize
     * @return mixed The sanitized input
     */
    public static function sanitize($input)
    {
        if (is_array($input)) {
            // Recursively sanitize arrays
            foreach ($input as $key => $value) {
                $input[$key] = self::sanitize($value);
            }
            return $input;
        }
        
        // Sanitize strings
        if (is_string($input)) {
            return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        }
        
        // Return other types as is
        return $input;
    }
    
    /**
     * Generate a CSRF token
     * 
     * @return string The CSRF token
     */
    public static function generateCsrfToken()
    {
        $token = self::generateToken(CSRF_TOKEN_LENGTH);
        $_SESSION[CSRF_TOKEN_NAME] = [
            'token' => $token,
            'expires' => time() + CSRF_EXPIRATION
        ];
        return $token;
    }
    
    /**
     * Verify a CSRF token
     * 
     * @param string $token The token to verify
     * @return bool True if the token is valid, false otherwise
     */
    public static function verifyCsrfToken($token)
    {
        if (empty($_SESSION[CSRF_TOKEN_NAME]) || empty($token)) {
            return false;
        }
        
        $session_token = $_SESSION[CSRF_TOKEN_NAME]['token'];
        $expiration = $_SESSION[CSRF_TOKEN_NAME]['expires'];
        
        if (time() > $expiration) {
            return false;
        }
        
        return hash_equals($session_token, $token);
    }
    
    /**
     * Check if a request exceeds the rate limit
     * 
     * @param string $key The rate limit key (e.g. IP address)
     * @param int $max The maximum number of requests
     * @param int $period The period in seconds
     * @return bool True if the rate limit is exceeded, false otherwise
     */
    public static function isRateLimited($key, $max = RATE_LIMIT_MAX_REQUESTS, $period = RATE_LIMIT_PERIOD)
    {
        if (!RATE_LIMIT_ENABLED) {
            return false;
        }
        
        // Get rate limit data from session
        if (!isset($_SESSION['rate_limits'][$key])) {
            $_SESSION['rate_limits'][$key] = [
                'count' => 1,
                'start_time' => time()
            ];
            return false;
        }
        
        $rate_limit = $_SESSION['rate_limits'][$key];
        $current_time = time();
        
        // Reset rate limit if period has passed
        if ($current_time - $rate_limit['start_time'] > $period) {
            $_SESSION['rate_limits'][$key] = [
                'count' => 1,
                'start_time' => $current_time
            ];
            return false;
        }
        
        // Increment count
        $_SESSION['rate_limits'][$key]['count']++;
        
        // Check if rate limit is exceeded
        if ($rate_limit['count'] > $max) {
            // Log rate limit exceeded
            Logger::warning("Rate limit exceeded: {$key}", [
                'ip' => get_client_ip(),
                'count' => $rate_limit['count'],
                'period' => $period
            ]);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Generate a secure random password
     * 
     * @param int $length The password length
     * @return string The random password
     */
    public static function generatePassword($length = 12)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+[]{}|;:,.<>?';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $index = random_int(0, strlen($chars) - 1);
            $password .= $chars[$index];
        }
        
        return $password;
    }
}