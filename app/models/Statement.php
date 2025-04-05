<?php
/**
 * Banking DVWA Project
 * Statement Model
 * 
 * Handles account statement generation and management.
 */

namespace App\Models;

use App\Core\Model;
use App\Core\Logger;

class Statement extends Model
{
    /**
     * @var string Database table name
     */
    protected $table = 'statements';
    
    /**
     * @var string Primary key field
     */
    protected $primaryKey = 'id';
    
    /**
     * @var array Fields that can be mass-assigned
     */
    protected $fillable = [
        'account_id',
        'statement_date',
        'start_date',
        'end_date',
        'opening_balance',
        'closing_balance',
        'filename',
        'created_at'
    ];
    
    /**
     * @var array Validation rules for fields
     */
    protected $rules = [
        'account_id' => 'required|numeric',
        'statement_date' => 'required',
        'start_date' => 'required',
        'end_date' => 'required',
        'opening_balance' => 'required|numeric',
        'closing_balance' => 'required|numeric',
        'filename' => 'required'
    ];
    
    /**
     * Generate a statement for an account
     * 
     * @param int $accountId Account ID
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @return array|bool Statement data or false if failed
     */
    public function generate($accountId, $startDate, $endDate)
    {
        // Begin transaction
        $this->db->beginTransaction();
        
        try {
            // Get account information
            $accountModel = new Account();
            $account = $accountModel->find($accountId);
            
            if (!$account) {
                throw new \Exception("Account not found");
            }
            
            // Get transactions for the period
            $transactionModel = new Transaction();
            $query = "SELECT * FROM " . DB_PREFIX . "transactions 
                      WHERE account_id = :account_id 
                      AND DATE(created_at) BETWEEN :start_date AND :end_date 
                      ORDER BY created_at";
                      
            $params = [
                'account_id' => $accountId,
                'start_date' => $startDate,
                'end_date' => $endDate
            ];
            
            $transactions = $transactionModel->query($query, $params);
            
            // Calculate opening balance (sum of all transactions before start date)
            $openingBalanceQuery = "SELECT SUM(amount) as balance FROM " . DB_PREFIX . "transactions 
                                   WHERE account_id = :account_id 
                                   AND DATE(created_at) < :start_date";
                                   
            $openingBalanceParams = [
                'account_id' => $accountId,
                'start_date' => $startDate
            ];
            
            $openingBalanceResult = $transactionModel->query($openingBalanceQuery, $openingBalanceParams, false);
            $openingBalance = $openingBalanceResult && isset($openingBalanceResult['balance']) 
                ? floatval($openingBalanceResult['balance']) 
                : 0.00;
            
            // Calculate closing balance
            $closingBalance = $openingBalance;
            foreach ($transactions as $transaction) {
                $closingBalance += $transaction['amount'];
            }
            
            // Generate filename
            $filename = 'statement_' . $account['account_number'] . '_' 
                      . str_replace('-', '', $startDate) . '_' 
                      . str_replace('-', '', $endDate) . '.pdf';
            
            // Create statement record
            $statementData = [
                'account_id' => $accountId,
                'statement_date' => date(DATE_FORMAT),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'opening_balance' => $openingBalance,
                'closing_balance' => $closingBalance,
                'filename' => $filename,
                'created_at' => date(DATETIME_FORMAT)
            ];
            
            $statementId = $this->create($statementData);
            
            if (!$statementId) {
                throw new \Exception("Failed to create statement record");
            }
            
            // Create statement PDF (would be implemented in a real system)
            // Here we're just simulating it by creating a file with placeholder text
            $statementPath = PUBLIC_PATH . '/assets/demo/sample_statements/' . $filename;
            $statementDir = dirname($statementPath);
            
            if (!file_exists($statementDir)) {
                mkdir($statementDir, 0755, true);
            }
            
            // Create placeholder file
            file_put_contents($statementPath, "Account Statement\n" 
                            . "Account: " . $account['account_number'] . "\n"
                            . "Period: " . $startDate . " to " . $endDate . "\n"
                            . "Opening Balance: " . $openingBalance . "\n"
                            . "Closing Balance: " . $closingBalance . "\n"
                            . "Transactions: " . count($transactions));
            
            // Commit transaction
            $this->db->commit();
            
            // Get statement record
            $statement = $this->find($statementId);
            
            // Add account and transactions
            $statement['account'] = $account;
            $statement['transactions'] = $transactions;
            
            Logger::info("Statement generated", [
                'statement_id' => $statementId,
                'account_id' => $accountId,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            
            return $statement;
        } catch (\Exception $e) {
            // Rollback transaction
            $this->db->rollback();
            Logger::error("Statement generation failed", [
                'error' => $e->getMessage(),
                'account_id' => $accountId,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            return false;
        }
    }
    
    /**
     * Get statements for an account
     * 
     * @param int $accountId Account ID
     * @return array Statement list
     */
    public function getForAccount($accountId)
    {
        return $this->findBy('account_id', $accountId);
    }
    
    /**
     * Get statements for a user
     * 
     * @param int $userId User ID
     * @return array Statement list
     */
    public function getForUser($userId)
    {
        $query = "SELECT s.*, a.account_number, a.account_type 
                  FROM " . DB_PREFIX . $this->table . " s 
                  JOIN " . DB_PREFIX . "accounts a ON s.account_id = a.id 
                  WHERE a.user_id = :user_id 
                  ORDER BY s.created_at DESC";
                  
        $params = ['user_id' => $userId];
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Get statement file path
     * 
     * @param array $statement Statement data
     * @return string File path
     */
    public function getFilePath($statement)
    {
        if (is_numeric($statement)) {
            $statement = $this->find($statement);
        }
        
        if (!$statement) {
            return '';
        }
        
        return PUBLIC_PATH . '/assets/demo/sample_statements/' . $statement['filename'];
    }
    
    /**
     * Get statement download URL
     * 
     * @param array $statement Statement data
     * @return string Download URL
     */
    public function getDownloadUrl($statement)
    {
        if (is_numeric($statement)) {
            $statement = $this->find($statement);
        }
        
        if (!$statement) {
            return '';
        }
        
        return APP_URL . '/public/assets/demo/sample_statements/' . $statement['filename'];
    }
    
    /**
     * Delete a statement
     * 
     * @param int $statementId Statement ID
     * @return bool Success status
     */
    public function delete($id)
    {
        // Get statement
        $statement = $this->find($id);
        
        if (!$statement) {
            return false;
        }
        
        // Delete file
        $filePath = $this->getFilePath($statement);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        // Delete record
        return parent::delete($id);
    }
}