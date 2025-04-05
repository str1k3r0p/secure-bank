<?php
/**
 * Banking DVWA Project
 * Account Controller
 * 
 * Handles account management functionality including dashboard,
 * account details, and account settings.
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Logger;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\Statement;

class AccountController extends Controller
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
     * @var Statement Statement model
     */
    private $statementModel;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        // Initialize models
        $this->accountModel = new Account();
        $this->transactionModel = new Transaction();
        $this->statementModel = new Statement();
        
        // Require authentication
        $this->requireAuth();
    }
    
    /**
     * Account dashboard
     */
    public function dashboard()
    {
        // Get user ID
        $userId = $_SESSION['user_id'];
        
        // Get active accounts for user
        $accounts = $this->accountModel->getActiveForUser($userId);
        
        // Get recent transactions
        $recentTransactions = $this->transactionModel->getRecentForUser($userId, 10);
        
        // Calculate total balance
        $totalBalance = $this->accountModel->getTotalBalanceForUser($userId);
        
        // Log access
        Logger::access("Account dashboard accessed", [
            'user_id' => $userId,
            'ip' => get_client_ip()
        ]);
        
        // Render dashboard
        $this->render('account/dashboard', [
            'title' => 'Account Dashboard',
            'accounts' => $accounts,
            'recentTransactions' => $recentTransactions,
            'totalBalance' => $totalBalance
        ]);
    }
    
    /**
     * Account details
     */
    public function details()
    {
        // Get account ID
        $accountId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$accountId) {
            $this->setFlash('error', 'Invalid account ID');
            $this->redirect('/account/dashboard');
            return;
        }
        
        // Verify ownership
        if (!$this->accountModel->isOwner($_SESSION['user_id'], $accountId)) {
            $this->setFlash('error', 'You do not have permission to view this account');
            $this->redirect('/account/dashboard');
            return;
        }
        
        // Get account with transactions
        $account = $this->accountModel->getWithTransactions($accountId, 20);
        
        if (!$account) {
            $this->setFlash('error', 'Account not found');
            $this->redirect('/account/dashboard');
            return;
        }
        
        // Get statements for this account
        $statements = $this->statementModel->getForAccount($accountId);
        
        // Log access
        Logger::access("Account details accessed", [
            'user_id' => $_SESSION['user_id'],
            'account_id' => $accountId,
            'ip' => get_client_ip()
        ]);
        
        // Render details page
        $this->render('account/details', [
            'title' => 'Account Details',
            'account' => $account,
            'statements' => $statements,
            'accountTypeLabel' => $this->accountModel->getTypeLabel($account['account_type'])
        ]);
    }
    
    /**
     * Account statement
     */
    public function statement()
    {
        // Get account ID
        $accountId = isset($_GET['account_id']) ? (int)$_GET['account_id'] : 0;
        
        if (!$accountId) {
            $this->setFlash('error', 'Invalid account ID');
            $this->redirect('/account/dashboard');
            return;
        }
        
        // Verify ownership
        if (!$this->accountModel->isOwner($_SESSION['user_id'], $accountId)) {
            $this->setFlash('error', 'You do not have permission to access this account');
            $this->redirect('/account/dashboard');
            return;
        }
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get form data
            $startDate = $this->input('start_date');
            $endDate = $this->input('end_date');
            
            // Validate dates
            if (empty($startDate) || empty($endDate)) {
                $this->setFlash('error', 'Please select both start and end dates');
                $this->redirect('/account/statement?account_id=' . $accountId);
                return;
            }
            
            if (strtotime($endDate) < strtotime($startDate)) {
                $this->setFlash('error', 'End date must be after start date');
                $this->redirect('/account/statement?account_id=' . $accountId);
                return;
            }
            
            // Generate statement
            $statement = $this->statementModel->generate($accountId, $startDate, $endDate);
            
            if ($statement) {
                $this->setFlash('success', 'Statement generated successfully');
                $this->redirect('/account/statement?account_id=' . $accountId . '&statement_id=' . $statement['id']);
                return;
            } else {
                $this->setFlash('error', 'Failed to generate statement');
                $this->redirect('/account/statement?account_id=' . $accountId);
                return;
            }
        }
        
        // Get account
        $account = $this->accountModel->find($accountId);
        
        if (!$account) {
            $this->setFlash('error', 'Account not found');
            $this->redirect('/account/dashboard');
            return;
        }
        
        // Get statements for this account
        $statements = $this->statementModel->getForAccount($accountId);
        
        // Check if viewing a specific statement
        $statementId = isset($_GET['statement_id']) ? (int)$_GET['statement_id'] : 0;
        $currentStatement = null;
        
        if ($statementId) {
            foreach ($statements as $stmt) {
                if ($stmt['id'] == $statementId) {
                    $currentStatement = $stmt;
                    
                    // Get transactions for this statement
                    $transactionModel = new Transaction();
                    $query = "SELECT * FROM " . DB_PREFIX . "transactions 
                              WHERE account_id = :account_id 
                              AND DATE(created_at) BETWEEN :start_date AND :end_date 
                              ORDER BY created_at";
                              
                    $params = [
                        'account_id' => $accountId,
                        'start_date' => $currentStatement['start_date'],
                        'end_date' => $currentStatement['end_date']
                    ];
                    
                    $transactions = $transactionModel->query($query, $params);
                    $currentStatement['transactions'] = $transactions;
                    
                    break;
                }
            }
        }
        
        // Log access
        Logger::access("Account statement accessed", [
            'user_id' => $_SESSION['user_id'],
            'account_id' => $accountId,
            'statement_id' => $statementId,
            'ip' => get_client_ip()
        ]);
        
        // Render statement page
        $this->render('account/statement', [
            'title' => 'Account Statement',
            'account' => $account,
            'statements' => $statements,
            'currentStatement' => $currentStatement,
            'downloadUrl' => $currentStatement ? $this->statementModel->getDownloadUrl($currentStatement) : null,
            'accountTypeLabel' => $this->accountModel->getTypeLabel($account['account_type'])
        ]);
    }
    
    /**
     * Account settings
     */
    public function settings()
    {
        // Get user ID
        $userId = $_SESSION['user_id'];
        
        // Get active accounts for user
        $accounts = $this->accountModel->getActiveForUser($userId);
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $this->input('action');
            
            switch ($action) {
                case 'new_account':
                    // Create new account
                    $accountType = $this->input('account_type');
                    
                    if (empty($accountType)) {
                        $this->setFlash('error', 'Please select an account type');
                        break;
                    }
                    
                    $accountData = [
                        'user_id' => $userId,
                        'account_type' => $accountType,
                        'balance' => 0,
                        'status' => 'active'
                    ];
                    
                    $newAccountId = $this->accountModel->create($accountData);
                    
                    if ($newAccountId) {
                        $this->setFlash('success', 'New account created successfully');
                    } else {
                        $this->setFlash('error', 'Failed to create new account');
                    }
                    break;
                
                case 'close_account':
                    // Close account
                    $accountId = (int)$this->input('account_id');
                    
                    if (!$accountId) {
                        $this->setFlash('error', 'Invalid account ID');
                        break;
                    }
                    
                    // Verify ownership
                    if (!$this->accountModel->isOwner($userId, $accountId)) {
                        $this->setFlash('error', 'You do not have permission to close this account');
                        break;
                    }
                    
                    // Check balance
                    $account = $this->accountModel->find($accountId);
                    
                    if ($account['balance'] > 0) {
                        $this->setFlash('error', 'Cannot close account with positive balance. Please transfer or withdraw all funds first.');
                        break;
                    }
                    
                    // Close account
                    $closed = $this->accountModel->close($accountId);
                    
                    if ($closed) {
                        $this->setFlash('success', 'Account closed successfully');
                    } else {
                        $this->setFlash('error', 'Failed to close account');
                    }
                    break;
            }
            
            $this->redirect('/account/settings');
            return;
        }
        
        // Log access
        Logger::access("Account settings accessed", [
            'user_id' => $userId,
            'ip' => get_client_ip()
        ]);
        
        // Render settings page
        $this->render('account/settings', [
            'title' => 'Account Settings',
            'accounts' => $accounts
        ]);
    }
}