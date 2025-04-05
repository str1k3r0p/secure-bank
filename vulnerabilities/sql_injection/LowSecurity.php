<?php
/**
 * Banking DVWA Project
 * SQL Injection - Low Security Implementation
 * 
 * This class implements the SQL injection vulnerability with low security.
 */

namespace Vulnerabilities\sql_injection;

use App\Core\Database;

class LowSecurity extends SqlInjection
{
    /**
     * Execute the vulnerability
     * 
     * @param array $input The input data
     * @return array|null The result
     */
    public function execute($input)
    {
        // Check if username is provided
        if (!isset($input['username']) || empty($input['username'])) {
            return null;
        }
        
        // Get username from input
        $username = $input['username'];
        
        // Connect to database
        $db = Database::getInstance();
        
        // Vulnerable query - directly concatenating user input
        $query = "SELECT id, username, first_name, last_name, email, role 
                 FROM " . DB_PREFIX . "users 
                 WHERE username = '$username'";
        
        // Execute query
        return $db->fetchAll($query);
    }
}