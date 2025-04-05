<?php
/**
 * Banking DVWA Project
 * SQL Injection Base Class
 * 
 * This class serves as the base for SQL injection vulnerability implementations.
 */

namespace Vulnerabilities\sql_injection;

use Vulnerabilities\VulnerabilityBase;

abstract class SqlInjection extends VulnerabilityBase
{
    /**
     * @var string The vulnerability name
     */
    protected $name = 'SQL Injection';
    
    /**
     * @var string The vulnerability description
     */
    protected $description = 'SQL Injection is a code injection technique that exploits a security vulnerability occurring in the database layer of an application. The vulnerability is present when user input is incorrectly filtered and directly included in SQL statements, allowing attackers to manipulate the SQL queries that the application sends to its database.';
    
    /**
     * Get the vulnerability type
     * 
     * @return string The vulnerability type
     */
    public function getType()
    {
        return 'sql_injection';
    }
    
    /**
     * Execute the vulnerability
     * 
     * @param array $input The input data
     * @return mixed The result
     */
    abstract public function execute($input);
}