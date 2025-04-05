<?php
/**
 * Banking DVWA Project
 * Brute Force - Low Security Implementation
 * 
 * This class implements the brute force vulnerability with low security measures.
 * It provides no protection against brute force attacks.
 */

namespace Vulnerabilities\bruteforce;

use App\Core\Database;
use App\Core\Logger;

class LowSecurity extends BruteForce
{
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
        
        // Initialize database
        $db = Database::getInstance();
        
        // VULNERABLE: Direct string concatenation in SQL query (SQL Injection)
        // No brute force protection at all
        $query = "SELECT id, username, first_name, last_name, email, role, status 
                  FROM " . DB_PREFIX . "users 
                  WHERE (username = '$username' OR email = '$username') 
                  AND password = '$password' 
                  AND status = 'active'";
        
        // Execute query
        $user = $db->fetch($query);
        
        // Log the attempt
        Logger::security("Brute force login attempt (LOW)", [
            'username' => $username,
            'success' => $user ? 'true' : 'false',
            'ip' => get_client_ip()
        ]);
        
        return $user;
    }
}