<?php
/**
 * Banking DVWA Project
 * Transaction Model
 * 
 * Handles financial transactions including deposits, withdrawals,
 * transfers, and transaction history.
 */

namespace App\Models;

use App\Core\Model;
use App\Core\Logger;

class Transaction extends Model
{
    /**
     * @var string Database table name
     */
    protected $table = 'transactions';
    
    /**
     * @var string Primary key field
     */
    protected $primaryKey = 'id';
    
    /**
     * @var array Fields that can be mass-assigned
     */
    protected $fillable = [
        'account_id',
        'to_account_id',
        'transaction_type',
        'amount',
        'description',
        'reference',
        'currency',
        'status',
        'created_at',
        'updated_at'
    ];
    
    /**
     * @var array Validation rules for fields
     */
    protected $rules = [
        'account_id' => 'required|numeric',
        'transaction_type' => 'required',
        'amount' => 'required|numeric',
        'reference' => 'required',
        'status' => 'required'
    ];
    
    /**
     * Create a new transaction
     * 
     * @param array $data Transaction data
     * @return int|bool New transaction ID or false if failed
     */
    public function create($data)
    {
        // Generate reference if not provided
        if (!isset($data['reference']) || empty($data['reference'])) {
            $data['reference'] = $this->generateReference();
        }
        
        // Set defaults
        if (!isset($data['status'])) {
            $data['status'] = STATUS_COMPLETED;
        }
        
        if (!isset($data['currency'])) {
            $data['currency'] = DEFAULT_CURRENCY;
        }
        
        // Set timestamps
        $data['created_at'] = date(DATETIME_FORMAT);
        
        // Create transaction
        $transactionId = parent::create($data);
        
        if ($transactionId) {
            Logger::info("Transaction created", [
                'transaction_id' => $transactionId,
                'account_id' => $data['account_id'],
                'type' => $data['transaction_type'],
                'amount' => $data['amount']
            ]);
        }
        
        return $transactionId;
    }
    
    /**
     * Update a transaction
     * 
     * @param int $id Transaction ID
     * @param array $data Transaction data
     * @return bool Success status
     */
    public function update($id, $data)
    {
        // Set updated timestamp
        $data['updated_at'] = date(DATETIME_FORMAT);
        
        // Update transaction
        $success = parent::update($id, $data);
        
        if ($success) {
            Logger::info("Transaction updated", ['transaction_id' => $id]);
        }
        
        return $success;
    }
    
    /**
     * Generate a unique transaction reference
     * 
     * @return string Transaction reference
     */
    public function generateReference()
    {
        $prefix = 'TX-' . date('YmdHis');
        $suffix = mt_rand(1000, 9999);
        
        return $prefix . $suffix;
    }
    
    /**
     * Create a deposit transaction
     * 
     * @param int $accountId Account ID
     * @param float $amount Amount to deposit
     * @param string $description Transaction description
     * @return int|bool New transaction ID or false if failed
     */
    public function deposit($accountId, $amount, $description = 'Deposit')
    {
        // Begin transaction
        $this->db->beginTransaction();
        
        try {
            // Create transaction record
            $transactionData = [
                'account_id' => $accountId,
                'transaction_type' => TRANSACTION_DEPOSIT,
                'amount' => abs($amount), // Ensure positive amount
                'description' => $description,
                'status' => STATUS_COMPLETED
            ];
            
            $transactionId = $this->create($transactionData);
            
            if (!$transactionId) {
                throw new \Exception("Failed to create transaction record");
            }
            
            // Update account balance
            $accountModel = new Account();
            $success = $accountModel->updateBalance($accountId, abs($amount));
            
            if (!$success) {
                throw new \Exception("Failed to update account balance");
            }
            
            // Commit transaction
            $this->db->commit();
            
            return $transactionId;
        } catch (\Exception $e) {
            // Rollback transaction
            $this->db->rollback();
            Logger::error("Deposit failed", ['error' => $e->getMessage(), 'account_id' => $accountId, 'amount' => $amount]);
            return false;
        }
    }
    
    /**
     * Create a withdrawal transaction
     * 
     * @param int $accountId Account ID
     * @param float $amount Amount to withdraw
     * @param string $description Transaction description
     * @return int|bool New transaction ID or false if failed
     */
    public function withdraw($accountId, $amount, $description = 'Withdrawal')
    {
        // Begin transaction
        $this->db->beginTransaction();
        
        try {
            // Check for sufficient funds
            $accountModel = new Account();
            $account = $accountModel->find($accountId);
            
            if (!$account) {
                throw new \Exception("Account not found");
            }
            
            if ($account['balance'] < abs($amount)) {
                throw new \Exception("Insufficient funds");
            }
            
            // Create transaction record
            $transactionData = [
                'account_id' => $accountId,
                'transaction_type' => TRANSACTION_WITHDRAWAL,
                'amount' => -abs($amount), // Ensure negative amount
                'description' => $description,
                'status' => STATUS_COMPLETED
            ];
            
            $transactionId = $this->create($transactionData);
            
            if (!$transactionId) {
                throw new \Exception("Failed to create transaction record");
            }
            
            // Update account balance
            $success = $accountModel->updateBalance($accountId, -abs($amount));
            
            if (!$success) {
                throw new \Exception("Failed to update account balance");
            }
            
            // Commit transaction
            $this->db->commit();
            
            return $transactionId;
        } catch (\Exception $e) {
            // Rollback transaction
            $this->db->rollback();
            Logger::error("Withdrawal failed", ['error' => $e->getMessage(), 'account_id' => $accountId, 'amount' => $amount]);
            return false;
        }
    }
    
    /**
     * Create a transfer transaction between accounts
     * 
     * @param int $fromAccountId Source account ID
     * @param int $toAccountId Destination account ID
     * @param float $amount Amount to transfer
     * @param string $description Transaction description
     * @return int|bool New transaction ID or false if failed
     */
    public function transfer($fromAccountId, $toAccountId, $amount, $description = 'Transfer')
    {
        // Begin transaction
        $this->db->beginTransaction();
        
        try {
            // Check for sufficient funds
            $accountModel = new Account();
            $fromAccount = $accountModel->find($fromAccountId);
            $toAccount = $accountModel->find($toAccountId);
            
            if (!$fromAccount || !$toAccount) {
                throw new \Exception("One or both accounts not found");
            }
            
            if ($fromAccount['balance'] < abs($amount)) {
                throw new \Exception("Insufficient funds");
            }
            
            // Create outgoing transaction record
            $outgoingData = [
                'account_id' => $fromAccountId,
                'to_account_id' => $toAccountId,
                'transaction_type' => TRANSACTION_TRANSFER,
                'amount' => -abs($amount), // Ensure negative amount
                'description' => $description,
                'status' => STATUS_COMPLETED
            ];
            
            $outgoingId = $this->create($outgoingData);
            
            if (!$outgoingId) {
                throw new \Exception("Failed to create outgoing transaction record");
            }
            
            // Create incoming transaction record
            $incomingData = [
                'account_id' => $toAccountId,
                'to_account_id' => $fromAccountId,
                'transaction_type' => TRANSACTION_TRANSFER,
                'amount' => abs($amount), // Ensure positive amount
                'description' => $description,
                'reference' => $outgoingData['reference'], // Use same reference
                'status' => STATUS_COMPLETED
            ];
            
            $incomingId = $this->create($incomingData);
            
            if (!$incomingId) {
                throw new \Exception("Failed to create incoming transaction record");
            }
            
            // Update account balances
            $fromSuccess = $accountModel->updateBalance($fromAccountId, -abs($amount));
            $toSuccess = $accountModel->updateBalance($toAccountId, abs($amount));
            
            if (!$fromSuccess || !$toSuccess) {
                throw new \Exception("Failed to update account balances");
            }
            
            // Commit transaction
            $this->db->commit();
            
            return $outgoingId;
        } catch (\Exception $e) {
            // Rollback transaction
            $this->db->rollback();
            Logger::error("Transfer failed", [
                'error' => $e->getMessage(),
                'from_account_id' => $fromAccountId,
                'to_account_id' => $toAccountId,
                'amount' => $amount
            ]);
            return false;
        }
    }
    
    /**
     * Get transactions for an account
     * 
     * @param int $accountId Account ID
     * @param int $limit Maximum number of transactions to return
     * @param int $offset Result offset for pagination
     * @return array Transaction list
     */
    public function getForAccount($accountId, $limit = 10, $offset = 0)
    {
        $query = "SELECT * FROM " . DB_PREFIX . $this->table . " 
                  WHERE account_id = :account_id 
                  ORDER BY created_at DESC 
                  LIMIT :limit OFFSET :offset";
                  
        $params = [
            'account_id' => $accountId,
            'limit' => $limit,
            'offset' => $offset
        ];
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Get recent transactions for all accounts of a user
     * 
     * @param int $userId User ID
     * @param int $limit Maximum number of transactions to return
     * @return array Transaction list
     */
    public function getRecentForUser($userId, $limit = 10)
    {
        $query = "SELECT t.*, a.account_number, a.account_type 
                  FROM " . DB_PREFIX . $this->table . " t 
                  JOIN " . DB_PREFIX . "accounts a ON t.account_id = a.id 
                  WHERE a.user_id = :user_id 
                  ORDER BY t.created_at DESC 
                  LIMIT :limit";
                  
        $params = [
            'user_id' => $userId,
            'limit' => $limit
        ];
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Get transaction type label
     * 
     * @param string $transactionType Transaction type code
     * @return string Human-readable transaction type
     */
    public function getTypeLabel($transactionType)
    {
        $types = [
            TRANSACTION_DEPOSIT => 'Deposit',
            TRANSACTION_WITHDRAWAL => 'Withdrawal',
            TRANSACTION_TRANSFER => 'Transfer',
            TRANSACTION_PAYMENT => 'Payment'
        ];
        
        return $types[$transactionType] ?? 'Unknown Transaction';
    }
    
    /**
     * Get transaction status label
     * 
     * @param string $status Status code
     * @return string Human-readable status
     */
    public function getStatusLabel($status)
    {
        $statuses = [
            STATUS_PENDING => 'Pending',
            STATUS_COMPLETED => 'Completed',
            STATUS_FAILED => 'Failed',
            STATUS_CANCELLED => 'Cancelled'
        ];
        
        return $statuses[$status] ?? 'Unknown Status';
    }
    
    /**
     * Count transactions for an account
     * 
     * @param int $accountId Account ID
     * @return int Transaction count
     */
    public function countForAccount($accountId)
    {
        return $this->count('account_id = :account_id', ['account_id' => $accountId]);
    }
}