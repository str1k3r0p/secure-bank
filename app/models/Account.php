<?php
/**
 * Banking DVWA Project
 * Account Model
 * 
 * Handles banking account operations including creation, balance
 * management, and account details.
 */

namespace App\Models;

use App\Core\Model;
use App\Core\Logger;

class Account extends Model
{
    /**
     * @var string Database table name
     */
    protected $table = 'accounts';
    
    /**
     * @var string Primary key field
     */
    protected $primaryKey = 'id';
    
    /**
     * @var array Fields that can be mass-assigned
     */
    protected $fillable = [
        'user_id',
        'account_number',
        'account_type',
        'balance',
        'currency',
        'status',
        'created_at',
        'updated_at'
    ];
    
    /**
     * @var array Validation rules for fields
     */
    protected $rules = [
        'user_id' => 'required|numeric',
        'account_number' => 'required|alpha_num|unique',
        'account_type' => 'required',
        'balance' => 'numeric',
        'currency' => 'required|alpha|max:3',
        'status' => 'required'
    ];
    
    /**
     * Create a new account
     * 
     * @param array $data Account data
     * @return int|bool New account ID or false if failed
     */
    public function create($data)
    {
        // Generate account number if not provided
        if (!isset($data['account_number']) || empty($data['account_number'])) {
            $data['account_number'] = $this->generateAccountNumber();
        }
        
        // Set defaults
        if (!isset($data['balance'])) {
            $data['balance'] = 0.00;
        }
        
        if (!isset($data['currency'])) {
            $data['currency'] = DEFAULT_CURRENCY;
        }
        
        if (!isset($data['status'])) {
            $data['status'] = 'active';
        }
        
        // Set timestamps
        $data['created_at'] = date(DATETIME_FORMAT);
        
        // Create account
        $accountId = parent::create($data);
        
        if ($accountId) {
            Logger::info("Account created", [
                'account_id' => $accountId,
                'user_id' => $data['user_id'],
                'account_type' => $data['account_type']
            ]);
        }
        
        return $accountId;
    }
    
    /**
     * Update an account
     * 
     * @param int $id Account ID
     * @param array $data Account data
     * @return bool Success status
     */
    public function update($id, $data)
    {
        // Set updated timestamp
        $data['updated_at'] = date(DATETIME_FORMAT);
        
        // Update account
        $success = parent::update($id, $data);
        
        if ($success) {
            Logger::info("Account updated", ['account_id' => $id]);
        }
        
        return $success;
    }
    
    /**
     * Create default account for a new user
     * 
     * @param int $userId User ID
     * @return int|bool New account ID or false if failed
     */
    public function createDefaultForUser($userId)
    {
        // Create default checking account
        $accountData = [
            'user_id' => $userId,
            'account_type' => ACCOUNT_CHECKING,
            'balance' => 1000.00, // Default starting balance
            'currency' => DEFAULT_CURRENCY,
            'status' => 'active'
        ];
        
        return $this->create($accountData);
    }
    
    /**
     * Generate a unique account number
     * 
     * @return string Account number
     */
    public function generateAccountNumber()
    {
        $prefix = date('Ymd');
        $suffix = mt_rand(1000, 9999);
        
        $accountNumber = $prefix . $suffix;
        
        // Ensure uniqueness
        while ($this->accountNumberExists($accountNumber)) {
            $suffix = mt_rand(1000, 9999);
            $accountNumber = $prefix . $suffix;
        }
        
        return $accountNumber;
    }
    
    /**
     * Check if account number exists
     * 
     * @param string $accountNumber Account number to check
     * @return bool True if exists
     */
    public function accountNumberExists($accountNumber)
    {
        return $this->findOneBy('account_number', $accountNumber) !== null;
    }
    
    /**
     * Get account by account number
     * 
     * @param string $accountNumber Account number
     * @return array|null Account data or null if not found
     */
    public function getByAccountNumber($accountNumber)
    {
        return $this->findOneBy('account_number', $accountNumber);
    }
    
    /**
     * Get account balance
     * 
     * @param int $accountId Account ID
     * @return float Current balance
     */
    public function getBalance($accountId)
    {
        $account = $this->find($accountId);
        return $account ? $account['balance'] : 0.00;
    }
    
    /**
     * Update account balance
     * 
     * @param int $accountId Account ID
     * @param float $amount Amount to add (positive) or subtract (negative)
     * @return bool Success status
     */
    public function updateBalance($accountId, $amount)
    {
        // Get current balance
        $account = $this->find($accountId);
        
        if (!$account) {
            Logger::error("Failed to update balance - account not found", ['account_id' => $accountId]);
            return false;
        }
        
        // Calculate new balance
        $newBalance = $account['balance'] + $amount;
        
        // Update balance
        $success = $this->update($accountId, ['balance' => $newBalance]);
        
        if ($success) {
            Logger::info("Account balance updated", [
                'account_id' => $accountId,
                'previous_balance' => $account['balance'],
                'new_balance' => $newBalance,
                'change' => $amount
            ]);
        }
        
        return $success;
    }
    
    /**
     * Get active accounts for a user
     * 
     * @param int $userId User ID
     * @return array Active accounts
     */
    public function getActiveForUser($userId)
    {
        $query = "SELECT * FROM " . DB_PREFIX . $this->table . " 
                  WHERE user_id = :user_id AND status = 'active' 
                  ORDER BY account_type, created_at";
                  
        $params = ['user_id' => $userId];
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Get account with recent transactions
     * 
     * @param int $accountId Account ID
     * @param int $limit Transaction limit (default: 10)
     * @return array|null Account data with transactions
     */
    public function getWithTransactions($accountId, $limit = 10)
    {
        // Get account
        $account = $this->find($accountId);
        
        if (!$account) {
            return null;
        }
        
        // Get transactions
        $transactionModel = new Transaction();
        $transactions = $transactionModel->getForAccount($accountId, $limit);
        
        // Add transactions to account data
        $account['transactions'] = $transactions;
        
        return $account;
    }
    
    /**
     * Check if user is account owner
     * 
     * @param int $userId User ID
     * @param int $accountId Account ID
     * @return bool True if user owns account
     */
    public function isOwner($userId, $accountId)
    {
        $account = $this->find($accountId);
        return $account && $account['user_id'] == $userId;
    }
    
    /**
     * Get account type label
     * 
     * @param string $accountType Account type code
     * @return string Human-readable account type
     */
    public function getTypeLabel($accountType)
    {
        $types = [
            ACCOUNT_CHECKING => 'Checking Account',
            ACCOUNT_SAVINGS => 'Savings Account',
            ACCOUNT_BUSINESS => 'Business Account'
        ];
        
        return $types[$accountType] ?? 'Unknown Account Type';
    }
    
    /**
     * Close an account (set status to closed)
     * 
     * @param int $accountId Account ID
     * @return bool Success status
     */
    public function close($accountId)
    {
        return $this->update($accountId, ['status' => 'closed']);
    }
    
    /**
     * Get total balance for all user accounts
     * 
     * @param int $userId User ID
     * @return float Total balance
     */
    public function getTotalBalanceForUser($userId)
    {
        $query = "SELECT SUM(balance) as total 
                  FROM " . DB_PREFIX . $this->table . " 
                  WHERE user_id = :user_id AND status = 'active'";
                  
        $params = ['user_id' => $userId];
        
        $result = $this->db->fetch($query, $params);
        
        return $result ? floatval($result['total']) : 0.00;
    }
}