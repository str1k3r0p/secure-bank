<?php
/**
 * Banking DVWA Project
 * User Model
 * 
 * Handles all user-related operations including authentication, 
 * registration, and profile management.
 */

namespace App\Models;

use App\Core\Model;
use App\Core\Security;
use App\Core\Logger;

class User extends Model
{
    /**
     * @var string Database table name
     */
    protected $table = 'users';
    
    /**
     * @var string Primary key field
     */
    protected $primaryKey = 'id';
    
    /**
     * @var array Fields that can be mass-assigned
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'first_name',
        'last_name',
        'role',
        'status',
        'last_login'
    ];
    
    /**
     * @var array Validation rules for fields
     */
    protected $rules = [
        'username' => 'required|alpha_num|min:3|max:50|unique',
        'email' => 'required|email|unique',
        'password' => 'required|min:8',
        'first_name' => 'required|alpha|max:50',
        'last_name' => 'required|alpha|max:50',
        'role' => 'required'
    ];
    
    /**
     * Create a new user
     * 
     * @param array $data User data
     * @return int|bool New user ID or false if failed
     */
    public function create($data)
    {
        // Validate data
        $errors = $this->validate($data);
        if (!empty($errors)) {
            Logger::error("User creation validation failed", ['errors' => $errors]);
            return false;
        }
        
        // Hash password
        if (isset($data['password'])) {
            $data['password'] = Security::hashPassword($data['password']);
        }
        
        // Set default role if not provided
        if (!isset($data['role']) || empty($data['role'])) {
            $data['role'] = ROLE_USER;
        }
        
        // Set status to active if not provided
        if (!isset($data['status']) || empty($data['status'])) {
            $data['status'] = 'active';
        }
        
        // Set created_at timestamp
        $data['created_at'] = date(DATETIME_FORMAT);
        
        // Create user
        $userId = parent::create($data);
        
        if ($userId) {
            Logger::info("User created successfully", ['user_id' => $userId]);
            
            // Create default account for user
            if ($data['role'] === ROLE_USER) {
                $accountModel = new Account();
                $accountModel->createDefaultForUser($userId);
            }
        }
        
        return $userId;
    }
    
    /**
     * Update a user
     * 
     * @param int $id User ID
     * @param array $data User data
     * @return bool Success status
     */
    public function update($id, $data)
    {
        // Handle password separately
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Security::hashPassword($data['password']);
        } elseif (isset($data['password']) && empty($data['password'])) {
            // Don't update empty password
            unset($data['password']);
        }
        
        // Set updated timestamp
        $data['updated_at'] = date(DATETIME_FORMAT);
        
        // Update user
        $success = parent::update($id, $data);
        
        if ($success) {
            Logger::info("User updated", ['user_id' => $id]);
        }
        
        return $success;
    }
    
    /**
     * Authenticate a user
     * 
     * @param string $username Username or email
     * @param string $password Password
     * @param string $securityLevel Security level for brute force vulnerability
     * @return array|bool User data or false if authentication failed
     */
    public function authenticate($username, $password, $securityLevel = 'high')
    {
        // Different authentication based on security level
        switch ($securityLevel) {
            case 'low':
                // VULNERABLE: Direct SQL query, no password hashing
                $query = "SELECT * FROM " . DB_PREFIX . $this->table . " 
                          WHERE (username = '$username' OR email = '$username') 
                          AND password = '$password' AND status = 'active' LIMIT 1";
                $user = $this->db->fetch($query);
                break;
                
            case 'medium':
                // SOMEWHAT SECURE: Parameterized query but still direct password comparison
                $query = "SELECT * FROM " . DB_PREFIX . $this->table . " 
                          WHERE (username = :username OR email = :username) 
                          AND password = :password AND status = 'active' LIMIT 1";
                $params = [
                    'username' => $username,
                    'password' => $password
                ];
                $user = $this->db->fetch($query, $params);
                break;
                
            case 'high':
            default:
                // SECURE: Parameterized query with password hashing
                $query = "SELECT * FROM " . DB_PREFIX . $this->table . " 
                          WHERE (username = :username OR email = :username) 
                          AND status = 'active' LIMIT 1";
                $params = ['username' => $username];
                $user = $this->db->fetch($query, $params);
                
                // Verify password if user found
                if ($user && !Security::verifyPassword($password, $user['password'])) {
                    $user = false;
                }
                break;
        }
        
        if ($user) {
            // Update last login time
            $this->update($user['id'], ['last_login' => date(DATETIME_FORMAT)]);
            
            // Remove password from user data
            unset($user['password']);
            
            Logger::info("User authenticated successfully", ['user_id' => $user['id']]);
        } else {
            Logger::warning("Failed authentication attempt", [
                'username' => $username,
                'ip' => get_client_ip(),
                'security_level' => $securityLevel
            ]);
        }
        
        return $user;
    }
    
    /**
     * Get user with accounts
     * 
     * @param int $id User ID
     * @return array|null User data with accounts
     */
    public function getWithAccounts($id)
    {
        // Get user
        $user = $this->find($id);
        
        if (!$user) {
            return null;
        }
        
        // Get accounts
        $accountModel = new Account();
        $accounts = $accountModel->findBy('user_id', $id);
        
        // Add accounts to user data
        $user['accounts'] = $accounts;
        
        return $user;
    }
    
    /**
     * Get user by username
     * 
     * @param string $username Username
     * @return array|null User data or null if not found
     */
    public function getByUsername($username)
    {
        return $this->findOneBy('username', $username);
    }
    
    /**
     * Get user by email
     * 
     * @param string $email Email address
     * @return array|null User data or null if not found
     */
    public function getByEmail($email)
    {
        return $this->findOneBy('email', $email);
    }
    
    /**
     * Check if username exists
     * 
     * @param string $username Username to check
     * @return bool True if exists
     */
    public function usernameExists($username)
    {
        return $this->findOneBy('username', $username) !== null;
    }
    
    /**
     * Check if email exists
     * 
     * @param string $email Email to check
     * @return bool True if exists
     */
    public function emailExists($email)
    {
        return $this->findOneBy('email', $email) !== null;
    }
    
    /**
     * Get user's full name
     * 
     * @param array $user User data
     * @return string Full name
     */
    public function getFullName($user)
    {
        if (is_numeric($user)) {
            $user = $this->find($user);
        }
        
        if (!$user) {
            return '';
        }
        
        return $user['first_name'] . ' ' . $user['last_name'];
    }
    
    /**
     * Get users by role
     * 
     * @param string $role Role to filter by
     * @return array Users with specified role
     */
    public function getByRole($role)
    {
        return $this->findBy('role', $role);
    }
    
    /**
     * Delete a user and all related data
     * 
     * @param int $id User ID
     * @return bool Success status
     */
    public function delete($id)
    {
        // Begin transaction
        $this->db->beginTransaction();
        
        try {
            // Get accounts
            $accountModel = new Account();
            $accounts = $accountModel->findBy('user_id', $id);
            
            // Delete accounts (transactions will be deleted by foreign key constraints)
            foreach ($accounts as $account) {
                $accountModel->delete($account['id']);
            }
            
            // Delete user
            $result = parent::delete($id);
            
            if ($result) {
                // Commit transaction
                $this->db->commit();
                Logger::info("User deleted", ['user_id' => $id]);
                return true;
            } else {
                // Rollback transaction
                $this->db->rollback();
                return false;
            }
        } catch (\Exception $e) {
            // Rollback transaction
            $this->db->rollback();
            Logger::error("Error deleting user", ['user_id' => $id, 'error' => $e->getMessage()]);
            return false;
        }
    }
}