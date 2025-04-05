<?php
/**
 * Banking DVWA Project
 * SQL Injection - Medium Security Implementation
 * 
 * This class implements the SQL injection vulnerability with medium security.
 */

namespace Vulnerabilities\sql_injection;

use App\Core\Database;

class MediumSecurity extends SqlInjection
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
        
        // Simple attempt to prevent SQL injection
        // Blacklist some common SQL characters
        $blacklist = array("'", "\"", ";", "--", "/*", "*/", "=", "<", ">", "UNION", "SELECT", "DROP", "DELETE", "INSERT", "UPDATE");
        foreach ($blacklist as $blacklisted) {
            // Case-insensitive replacement
            $username = str_ireplace($blacklisted, "", $username);
        }
        
        // Connect to database
        $db = Database::getInstance();
        
        // Query is still vulnerable due to incomplete filtering
        $query = "SELECT id, username, first_name, last_name, email, role 
                 FROM " . DB_PREFIX . "users 
                 WHERE username = '$username'";
        
        // Execute query
        return $db->fetchAll($query);
    }
}