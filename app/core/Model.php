<?php
/**
 * Banking DVWA Project
 * Base Model Class
 * 
 * This class serves as the base for all models in the application.
 */

namespace App\Core;

class Model
{
    /**
     * @var Database The database instance
     */
    protected $db;
    
    /**
     * @var string The table name
     */
    protected $table;
    
    /**
     * @var string The primary key
     */
    protected $primaryKey = 'id';
    
    /**
     * @var array The fillable fields
     */
    protected $fillable = [];
    
    /**
     * @var array The field validation rules
     */
    protected $rules = [];
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Find a record by ID
     * 
     * @param int $id The ID to find
     * @return array|null The record or null if not found
     */
    public function find($id)
    {
        $query = "SELECT * FROM " . DB_PREFIX . $this->table . " WHERE " . $this->primaryKey . " = :id LIMIT 1";
        $params = ['id' => $id];
        
        return $this->db->fetch($query, $params);
    }
    
    /**
     * Find records by a field value
     * 
     * @param string $field The field to search by
     * @param mixed $value The value to match
     * @return array The matching records
     */
    public function findBy($field, $value)
    {
        $query = "SELECT * FROM " . DB_PREFIX . $this->table . " WHERE " . $field . " = :value";
        $params = ['value' => $value];
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Find a single record by a field value
     * 
     * @param string $field The field to search by
     * @param mixed $value The value to match
     * @return array|null The record or null if not found
     */
    public function findOneBy($field, $value)
    {
        $query = "SELECT * FROM " . DB_PREFIX . $this->table . " WHERE " . $field . " = :value LIMIT 1";
        $params = ['value' => $value];
        
        return $this->db->fetch($query, $params);
    }
    
    /**
     * Get all records
     * 
     * @param string $orderBy The field to order by
     * @param string $direction The sort direction (ASC or DESC)
     * @return array All records
     */
    public function all($orderBy = null, $direction = 'ASC')
    {
        $query = "SELECT * FROM " . DB_PREFIX . $this->table;
        
        if ($orderBy) {
            $query .= " ORDER BY " . $orderBy . " " . $direction;
        }
        
        return $this->db->fetchAll($query);
    }
    
    /**
     * Create a new record
     * 
     * @param array $data The record data
     * @return int|bool The new record ID or false on failure
     */
    public function create($data)
    {
        // Filter only fillable fields
        $filtered_data = array_intersect_key($data, array_flip($this->fillable));
        
        // Prepare the query
        $fields = array_keys($filtered_data);
        $placeholders = array_map(function($field) {
            return ':' . $field;
        }, $fields);
        
        $query = "INSERT INTO " . DB_PREFIX . $this->table . " (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        // Execute the query
        $result = $this->db->execute($query, $filtered_data);
        
        // Return the last insert ID if successful
        return $result ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Update a record
     * 
     * @param int $id The record ID
     * @param array $data The record data
     * @return bool True on success, false on failure
     */
    public function update($id, $data)
    {
        // Filter only fillable fields
        $filtered_data = array_intersect_key($data, array_flip($this->fillable));
        
        // Prepare the query
        $set = [];
        foreach ($filtered_data as $field => $value) {
            $set[] = $field . ' = :' . $field;
        }
        
        $query = "UPDATE " . DB_PREFIX . $this->table . " SET " . implode(', ', $set) . " WHERE " . $this->primaryKey . " = :id";
        
        // Add the ID to the parameters
        $filtered_data['id'] = $id;
        
        // Execute the query
        return $this->db->execute($query, $filtered_data);
    }
    
    /**
     * Delete a record
     * 
     * @param int $id The record ID
     * @return bool True on success, false on failure
     */
    public function delete($id)
    {
        $query = "DELETE FROM " . DB_PREFIX . $this->table . " WHERE " . $this->primaryKey . " = :id";
        $params = ['id' => $id];
        
        return $this->db->execute($query, $params);
    }
    
    /**
     * Count records
     * 
     * @param string $condition Optional WHERE condition
     * @param array $params Optional parameters for the condition
     * @return int The number of records
     */
    public function count($condition = '', $params = [])
    {
        $query = "SELECT COUNT(*) as count FROM " . DB_PREFIX . $this->table;
        
        if (!empty($condition)) {
            $query .= " WHERE " . $condition;
        }
        
        $result = $this->db->fetch($query, $params);
        
        return $result ? $result['count'] : 0;
    }
    
    /**
     * Paginate records
     * 
     * @param int $page The page number
     * @param int $perPage The number of items per page
     * @param string $orderBy The field to order by
     * @param string $direction The sort direction (ASC or DESC)
     * @return array The paginated records
     */
    public function paginate($page = 1, $perPage = ITEMS_PER_PAGE, $orderBy = null, $direction = 'ASC')
    {
        $offset = ($page - 1) * $perPage;
        
        $query = "SELECT * FROM " . DB_PREFIX . $this->table;
        
        if ($orderBy) {
            $query .= " ORDER BY " . $orderBy . " " . $direction;
        }
        
        $query .= " LIMIT " . $perPage . " OFFSET " . $offset;
        
        $items = $this->db->fetchAll($query);
        $total = $this->count();
        
        return [
            'items' => $items,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }
    
    /**
     * Validate data against the model's rules
     * 
     * @param array $data The data to validate
     * @return array The validation errors
     */
    public function validate($data)
    {
        $errors = [];
        
        foreach ($this->rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            // Check if the field exists and if it's required
            if (strpos($rule, 'required') !== false && empty($value)) {
                $errors[$field] = 'The ' . $field . ' field is required.';
                continue;
            }
            
            // If the field is empty but not required, skip other validations
            if (empty($value) && strpos($rule, 'required') === false) {
                continue;
            }
            
            // Check individual rules
            $rule_parts = explode('|', $rule);
            foreach ($rule_parts as $part) {
                if ($part === 'required') {
                    // Already checked
                    continue;
                } elseif ($part === 'email') {
                    if (!is_valid_email($value)) {
                        $errors[$field] = 'The ' . $field . ' must be a valid email address.';
                    }
                } elseif (strpos($part, 'min:') === 0) {
                    $min = substr($part, 4);
                    if (strlen($value) < $min) {
                        $errors[$field] = 'The ' . $field . ' must be at least ' . $min . ' characters.';
                    }
                } elseif (strpos($part, 'max:') === 0) {
                    $max = substr($part, 4);
                    if (strlen($value) > $max) {
                        $errors[$field] = 'The ' . $field . ' may not be greater than ' . $max . ' characters.';
                    }
                } elseif ($part === 'numeric') {
                    if (!is_numeric($value)) {
                        $errors[$field] = 'The ' . $field . ' must be a number.';
                    }
                } elseif ($part === 'alpha') {
                    if (!ctype_alpha($value)) {
                        $errors[$field] = 'The ' . $field . ' may only contain letters.';
                    }
                } elseif ($part === 'alpha_num') {
                    if (!ctype_alnum($value)) {
                        $errors[$field] = 'The ' . $field . ' may only contain letters and numbers.';
                    }
                } elseif (strpos($part, 'same:') === 0) {
                    $other_field = substr($part, 5);
                    $other_value = $data[$other_field] ?? null;
                    if ($value !== $other_value) {
                        $errors[$field] = 'The ' . $field . ' and ' . $other_field . ' must match.';
                    }
                } elseif ($part === 'unique') {
                    $existing = $this->findOneBy($field, $value);
                    if ($existing) {
                        $errors[$field] = 'The ' . $field . ' has already been taken.';
                    }
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Execute a custom query
     * 
     * @param string $query The SQL query
     * @param array $params The query parameters
     * @param bool $fetchAll Whether to fetch all results or just one
     * @return mixed The query result
     */
    public function query($query, $params = [], $fetchAll = true)
    {
        return $fetchAll ? $this->db->fetchAll($query, $params) : $this->db->fetch($query, $params);
    }
    
    /**
     * Execute a custom statement
     * 
     * @param string $query The SQL query
     * @param array $params The query parameters
     * @return bool True on success, false on failure
     */
    public function execute($query, $params = [])
    {
        return $this->db->execute($query, $params);
    }
}