<?php
/**
 * Banking DVWA Project
 * Database Class
 * 
 * This class handles database connections and queries.
 */

namespace App\Core;

class Database
{
    /**
     * @var Database The database instance (singleton)
     */
    private static $instance = null;
    
    /**
     * @var \PDO The PDO connection
     */
    private $connection;
    
    /**
     * Constructor
     */
    private function __construct()
    {
        try {
            // Create a new PDO connection
            $this->connection = new \PDO(DB_DSN, DB_USER, DB_PASS, DB_OPTIONS);
        } catch (\PDOException $e) {
            // Log the error
            $error_message = "Database connection error: " . $e->getMessage();
            log_message($error_message, 'error', LOGS_PATH . '/error.log');
            
            // Throw the exception
            throw new \Exception($error_message);
        }
    }
    
    /**
     * Get the database instance
     * 
     * @return Database The database instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Get the PDO connection
     * 
     * @return \PDO The PDO connection
     */
    public function getConnection()
    {
        return $this->connection;
    }
    
    /**
     * Execute a query and fetch a single row
     * 
     * @param string $query The SQL query
     * @param array $params The query parameters
     * @return array|null The result row or null if not found
     */
    public function fetch($query, $params = [])
    {
        try {
            $statement = $this->connection->prepare($query);
            $statement->execute($params);
            
            return $statement->fetch();
        } catch (\PDOException $e) {
            // Log the error
            $error_message = "Database query error: " . $e->getMessage();
            log_message($error_message, 'error', LOGS_PATH . '/error.log');
            
            return null;
        }
    }
    
    /**
     * Execute a query and fetch all rows
     * 
     * @param string $query The SQL query
     * @param array $params The query parameters
     * @return array The result rows
     */
    public function fetchAll($query, $params = [])
    {
        try {
            $statement = $this->connection->prepare($query);
            $statement->execute($params);
            
            return $statement->fetchAll();
        } catch (\PDOException $e) {
            // Log the error
            $error_message = "Database query error: " . $e->getMessage();
            log_message($error_message, 'error', LOGS_PATH . '/error.log');
            
            return [];
        }
    }
    
    /**
     * Execute a query
     * 
     * @param string $query The SQL query
     * @param array $params The query parameters
     * @return bool True on success, false on failure
     */
    public function execute($query, $params = [])
    {
        try {
            $statement = $this->connection->prepare($query);
            return $statement->execute($params);
        } catch (\PDOException $e) {
            // Log the error
            $error_message = "Database query error: " . $e->getMessage();
            log_message($error_message, 'error', LOGS_PATH . '/error.log');
            
            return false;
        }
    }
    
    /**
     * Get the last insert ID
     * 
     * @return string The last insert ID
     */
    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Begin a transaction
     * 
     * @return bool True on success, false on failure
     */
    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Commit a transaction
     * 
     * @return bool True on success, false on failure
     */
    public function commit()
    {
        return $this->connection->commit();
    }
    
    /**
     * Rollback a transaction
     * 
     * @return bool True on success, false on failure
     */
    public function rollback()
    {
        return $this->connection->rollBack();
    }
    
    /**
     * Get the number of affected rows
     * 
     * @param \PDOStatement $statement The PDO statement
     * @return int The number of affected rows
     */
    public function affectedRows($statement)
    {
        return $statement->rowCount();
    }
    
    /**
     * Quote a string for use in a query
     * 
     * @param string $string The string to quote
     * @return string The quoted string
     */
    public function quote($string)
    {
        return $this->connection->quote($string);
    }
    
    /**
     * Create a table if it doesn't exist
     * 
     * @param string $table The table name
     * @param string $schema The table schema
     * @return bool True on success, false on failure
     */
    public function createTable($table, $schema)
    {
        $query = "CREATE TABLE IF NOT EXISTS " . DB_PREFIX . $table . " (" . $schema . ")";
        return $this->execute($query);
    }
    
    /**
     * Drop a table if it exists
     * 
     * @param string $table The table name
     * @return bool True on success, false on failure
     */
    public function dropTable($table)
    {
        $query = "DROP TABLE IF EXISTS " . DB_PREFIX . $table;
        return $this->execute($query);
    }
    
    /**
     * Check if a table exists
     * 
     * @param string $table The table name
     * @return bool True if the table exists, false otherwise
     */
    public function tableExists($table)
    {
        $query = "SHOW TABLES LIKE '" . DB_PREFIX . $table . "'";
        $result = $this->fetch($query);
        
        return !empty($result);
    }
    
    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}
    
    /**
     * Prevent unserializing of the instance
     */
    private function __wakeup() {}
}