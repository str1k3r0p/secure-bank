<?php
/**
 * Banking DVWA Project
 * Transaction Controller
 * 
 * Handles transaction operations including deposits, withdrawals,
 * transfers, and transaction history.
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Logger;
use App\Models\Account;
use App\Models\Transaction;

class TransactionController extends Controller
{
    /**
     * @var Account Account model
     */
    private $accountModel;
    
    /**
     * @var Transaction Transaction model
     */
    private $transactionModel;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        // Initialize models
        $this->accountModel = new Account();
        $this->transactionModel = new Transaction();
        
        // Require authentication
        $this->requireAuth();
    }
    
    /**
     * New transaction
     */
    public function new()
    {
        // Get user ID
        $userId = $_SESSION['user_id'];
        
        // Get active accounts for user
        $accounts = $this->accountModel->getActiveForUser($userId);
        
        // Get account ID from query string if provided
        $selectedAccountId = isset($_GET['account_id']) ? (int)$_GET['account_id'] : 0;
        
        // Selected account
        $selectedAccount = null;
        
        // Check if selected account exists and belongs to user
        if ($selectedAccountId) {
            foreach ($accounts as $account) {
                if ($account['id'] == $selectedAccountId) {
                    $selectedAccount = $account;
                    break;
                }
            }
        }
        
        // If no account is selected or invalid, use first account
        if (!$selectedAccount && !empty($accounts)) {
            $selectedAccount = $accounts[0];
            $selectedAccountId = $selectedAccount['id'];
        }
        
        // Get transaction type from query string if provided
        $transactionType = isset($_GET['type']) ? $_GET['type'] : '';
        
        // Validate transaction type
        $validTypes = ['deposit', 'withdrawal', 'transfer'];
        if (!in_array($transactionType, $validTypes)) {
            $transactionType = 'deposit'; // Default type
        }
        
        // Log access
        Logger::access("New transaction page accessed", [
            'user_id' => $userId,
            'account_id' => $selectedAccountId,
            'transaction_type' => $transactionType,
            'ip' => get_client_ip()
        ]);
        
        // Render new transaction page
        $this->render('transaction/new', [
            'title' => 'New Transaction',
            'accounts' => $accounts,
            'selectedAccount' => $selectedAccount,
            'transactionType' => $transactionType,
            'accountTypeLabel' => $selectedAccount ? $this->accountModel->getTypeLabel($selectedAccount['account_type']) : null
        ]);
    }
    
    /**
     * Process transaction
     */
    public function process()
    {
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/transaction/new');
            return;
        }
        
        // Get form data
        $accountId = (int)$this->input('account_id');
        $transactionType = $this->input('transaction_type');
        $amount = (float)$this->input('amount');
        $description = $this->input('description');
        $toAccountNumber = $this->input('to_account_number');
        
        // Validate data
        $errors = [];
        
        if (!$accountId) {
            $errors['account_id'] = 'Please select an account';
        }
        
        if (!in_array($transactionType, ['deposit', 'withdrawal', 'transfer'])) {
            $errors['transaction_type'] = 'Invalid transaction type';
        }
        
        if ($amount <= 0) {
            $errors['amount'] = 'Amount must be greater than zero';
        }
        
        // Verify account ownership
        if ($accountId && !$this->accountModel->isOwner($_SESSION['user_id'], $accountId)) {
            $errors['account_id'] = 'You do not have permission to use this account';
        }
        
        // Check account balance for withdrawals and transfers
        if (($transactionType === 'withdrawal' || $transactionType === 'transfer') && $accountId) {
            $balance = $this->accountModel->getBalance($accountId);
            
            if ($balance < $amount) {
                $errors['amount'] = 'Insufficient funds';
            }
        }
        
        // Check destination account for transfers
        $toAccountId = null;
        if ($transactionType === 'transfer') {
            if (empty($toAccountNumber)) {
                $errors['to_account_number'] = 'Please enter destination account number';
            } else {
                $toAccount = $this->accountModel->getByAccountNumber($toAccountNumber);
                
                if (!$toAccount) {
                    $errors['to_account_number'] = 'Destination account not found';
                } elseif ($toAccount['id'] == $accountId) {
                    $errors['to_account_number'] = 'Cannot transfer to the same account';
                } elseif ($toAccount['status'] !== 'active') {
                    $errors['to_account_number'] = 'Destination account is not active';
                } else {
                    $toAccountId = $toAccount['id'];
                }
            }
        }
        
        // If validation fails, show the form again with errors
        if (!empty($errors)) {
            // Get active accounts for user
            $accounts = $this->accountModel->getActiveForUser($_SESSION['user_id']);
            
            // Get selected account
            $selectedAccount = null;
            foreach ($accounts as $account) {
                if ($account['id'] == $accountId) {
                    $selectedAccount = $account;
                    break;
                }
            }
            
            $this->render('transaction/new', [
                'title' => 'New Transaction',
                'accounts' => $accounts,
                'selectedAccount' => $selectedAccount,
                'transactionType' => $transactionType,
                'amount' => $amount,
                'description' => $description,
                'toAccountNumber' => $toAccountNumber,
                'errors' => $errors,
                'accountTypeLabel' => $selectedAccount ? $this->accountModel->getTypeLabel($selectedAccount['account_type']) : null
            ]);
            
            return;
        }
        
        // Process transaction based on type
        $result = false;
        $standardDescription = $description ?: ucfirst($transactionType);
        
        switch ($transactionType) {
            case 'deposit':
                $result = $this->transactionModel->deposit($accountId, $amount, $standardDescription);
                break;
                
            case 'withdrawal':
                $result = $this->transactionModel->withdraw($accountId, $amount, $standardDescription);
                break;
                
            case 'transfer':
                $result = $this->transactionModel->transfer($accountId, $toAccountId, $amount, $standardDescription);
                break;
        }
        
        // Redirect based on result
        if ($result) {
            // Store transaction details in session for confirmation page
            $_SESSION['transaction_details'] = [
                'transaction_id' => $result,
                'account_id' => $accountId,
                'transaction_type' => $transactionType,
                'amount' => $amount,
                'description' => $standardDescription,
                'to_account_number' => $toAccountNumber,
                'timestamp' => time()
            ];
            
            $this->setFlash('success', ucfirst($transactionType) . ' successful');
            $this->redirect('/transaction/confirm');
        } else {
            $this->setFlash('error', 'Transaction failed. Please try again.');
            $this->redirect('/transaction/new?account_id=' . $accountId . '&type=' . $transactionType);
        }
    }
    
    /**
     * Transaction confirmation
     */
    public function confirm()
    {
        // Check if transaction details exist in session
        if (!isset($_SESSION['transaction_details'])) {
            $this->redirect('/transaction/new');
            return;
        }
        
        // Get transaction details
        $transactionDetails = $_SESSION['transaction_details'];
        
        // Verify transaction timestamp (prevent reusing confirmation page)
        if (time() - $transactionDetails['timestamp'] > 300) {
            // More than 5 minutes old, clear details
            unset($_SESSION['transaction_details']);
            $this->setFlash('error', 'Transaction confirmation expired');
            $this->redirect('/transaction/new');
            return;
        }
        
        // Get account information
        $account = $this->accountModel->find($transactionDetails['account_id']);
        
        // Get destination account if transfer
        $toAccount = null;
        if ($transactionDetails['transaction_type'] === 'transfer' && !empty($transactionDetails['to_account_number'])) {
            $toAccount = $this->accountModel->getByAccountNumber($transactionDetails['to_account_number']);
        }
        
        // Get transaction record
        $transaction = $this->transactionModel->find($transactionDetails['transaction_id']);
        
        // Log access
        Logger::access("Transaction confirmation viewed", [
            'user_id' => $_SESSION['user_id'],
            'transaction_id' => $transactionDetails['transaction_id'],
            'ip' => get_client_ip()
        ]);
        
        // Render confirmation page
        $this->render('transaction/confirmation', [
            'title' => 'Transaction Confirmation',
            'transaction' => $transaction,
            'account' => $account,
            'toAccount' => $toAccount,
            'details' => $transactionDetails,
            'typeLabel' => $this->transactionModel->getTypeLabel($transaction['transaction_type']),
            'accountTypeLabel' => $this->accountModel->getTypeLabel($account['account_type'])
        ]);
        
        // Clear transaction details
        unset($_SESSION['transaction_details']);
    }
    
    /**
     * Transaction history
     */
    public function history()
    {
        // Get user ID
        $userId = $_SESSION['user_id'];
        
        // Get active accounts for user
        $accounts = $this->accountModel->getActiveForUser($userId);
        
        // Get account ID from query string if provided
        $selectedAccountId = isset($_GET['account_id']) ? (int)$_GET['account_id'] : 0;
        
        // Selected account
        $selectedAccount = null;
        
        // Check if selected account exists and belongs to user
        if ($selectedAccountId) {
            foreach ($accounts as $account) {
                if ($account['id'] == $selectedAccountId) {
                    $selectedAccount = $account;
                    break;
                }
            }
        }
        
        // If no account is selected or invalid, use first account
        if (!$selectedAccount && !empty($accounts)) {
            $selectedAccount = $accounts[0];
            $selectedAccountId = $selectedAccount['id'];
        }
        
        // Get pagination parameters
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Get transactions for selected account
        $transactions = [];
        $totalTransactions = 0;
        
        if ($selectedAccount) {
            $transactions = $this->transactionModel->getForAccount($selectedAccountId, $perPage, $offset);
            $totalTransactions = $this->transactionModel->countForAccount($selectedAccountId);
        }
        
        // Calculate pagination
        $totalPages = ceil($totalTransactions / $perPage);
        
        // Log access
        Logger::access("Transaction history accessed", [
            'user_id' => $userId,
            'account_id' => $selectedAccountId,
            'page' => $page,
            'ip' => get_client_ip()
        ]);
        
        // Render transaction history page
        $this->render('transaction/history', [
            'title' => 'Transaction History',
            'accounts' => $accounts,
            'selectedAccount' => $selectedAccount,
            'transactions' => $transactions,
            'page' => $page,
            'totalPages' => $totalPages,
            'accountTypeLabel' => $selectedAccount ? $this->accountModel->getTypeLabel($selectedAccount['account_type']) : null
        ]);
    }
}