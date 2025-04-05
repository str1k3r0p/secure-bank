<?php
/**
 * Banking DVWA Project
 * Admin Controller
 * 
 * Handles administrative functionality including dashboard,
 * user management, and system settings.
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Logger;
use App\Models\User;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\SecurityLevel;

class AdminController extends Controller
{
    /**
     * @var User User model
     */
    private $userModel;
    
    /**
     * @var Account Account model
     */
    private $accountModel;
    
    /**
     * @var Transaction Transaction model
     */
    private $transactionModel;
    
    /**
     * @var SecurityLevel Security level model
     */
    private $securityLevelModel;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        // Initialize models
        $this->userModel = new User();
        $this->accountModel = new Account();
        $this->transactionModel = new Transaction();
        $this->securityLevelModel = new SecurityLevel();
        
        // Require authentication and admin role
        $this->requireAuth('/admin/login');
        $this->requireRole(ROLE_ADMIN, '/admin/login');
    }
    
    /**
     * Admin dashboard
     */
    public function dashboard()
    {
        // Get user count
        $userCount = $this->userModel->count();
        
        // Get account count
        $accountCount = $this->accountModel->count();
        
        // Get transaction count
        $transactionCount = $this->transactionModel->count();
        
        // Get recent transactions
        $recentTransactions = $this->transactionModel->query(
            "SELECT t.*, a.account_number, u.username 
             FROM " . DB_PREFIX . "transactions t
             JOIN " . DB_PREFIX . "accounts a ON t.account_id = a.id
             JOIN " . DB_PREFIX . "users u ON a.user_id = u.id
             ORDER BY t.created_at DESC LIMIT 10"
        );
        
        // Get new users
        $newUsers = $this->userModel->query(
            "SELECT * FROM " . DB_PREFIX . "users 
             ORDER BY created_at DESC LIMIT 5"
        );
        
        // Get security levels
        $securityLevels = $this->securityLevelModel->getAllLevels();
        
        // Log access
        Logger::access("Admin dashboard accessed", [
            'user_id' => $_SESSION['user_id'],
            'ip' => get_client_ip()
        ]);
        
        // Render dashboard
        $this->render('admin/dashboard', [
            'title' => 'Admin Dashboard',
            'userCount' => $userCount,
            'accountCount' => $accountCount,
            'transactionCount' => $transactionCount,
            'recentTransactions' => $recentTransactions,
            'newUsers' => $newUsers,
            'securityLevels' => $securityLevels
        ]);
    }
    
    /**
     * User management
     */
    public function users()
    {
        // Handle actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $this->input('action');
            $userId = $this->input('user_id');
            
            switch ($action) {
                case 'delete':
                    if ($userId && $this->userModel->delete($userId)) {
                        $this->setFlash('success', 'User deleted successfully');
                    } else {
                        $this->setFlash('error', 'Failed to delete user');
                    }
                    break;
                    
                case 'update_status':
                    $status = $this->input('status');
                    if ($userId && $status && $this->userModel->update($userId, ['status' => $status])) {
                        $this->setFlash('success', 'User status updated successfully');
                    } else {
                        $this->setFlash('error', 'Failed to update user status');
                    }
                    break;
                    
                case 'update_role':
                    $role = $this->input('role');
                    if ($userId && $role && $this->userModel->update($userId, ['role' => $role])) {
                        $this->setFlash('success', 'User role updated successfully');
                    } else {
                        $this->setFlash('error', 'Failed to update user role');
                    }
                    break;
            }
            
            $this->redirect('/admin/users');
            return;
        }
        
        // Get pagination parameters
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $search = $this->input('search', '');
        
        // Get users with pagination
        if (!empty($search)) {
            $where = "username LIKE :search OR email LIKE :search OR first_name LIKE :search OR last_name LIKE :search";
            $params = ['search' => "%{$search}%"];
            $total = $this->userModel->count($where, $params);
            
            $query = "SELECT * FROM " . DB_PREFIX . "users 
                     WHERE {$where} 
                     ORDER BY id DESC 
                     LIMIT " . ITEMS_PER_PAGE . " 
                     OFFSET " . ($page - 1) * ITEMS_PER_PAGE;
                     
            $users = $this->userModel->query($query, $params);
        } else {
            $total = $this->userModel->count();
            
            $query = "SELECT * FROM " . DB_PREFIX . "users 
                     ORDER BY id DESC 
                     LIMIT " . ITEMS_PER_PAGE . " 
                     OFFSET " . ($page - 1) * ITEMS_PER_PAGE;
                     
            $users = $this->userModel->query($query);
        }
        
        // Calculate total pages
        $totalPages = ceil($total / ITEMS_PER_PAGE);
        
        // Log access
        Logger::access("Admin users page accessed", [
            'user_id' => $_SESSION['user_id'],
            'ip' => get_client_ip(),
            'page' => $page,
            'search' => $search
        ]);
        
        // Render users page
        $this->render('admin/users', [
            'title' => 'User Management',
            'users' => $users,
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'total' => $total
        ]);
    }
    
    /**
     * Transaction management
     */
    public function transactions()
    {
        // Get pagination parameters
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $search = $this->input('search', '');
        $filter = $this->input('filter', '');
        
        // Build query based on search and filter
        $where = "";
        $params = [];
        
        if (!empty($search)) {
            $where .= "t.reference LIKE :search OR a.account_number LIKE :search OR u.username LIKE :search";
            $params['search'] = "%{$search}%";
        }
        
        if (!empty($filter)) {
            if (!empty($where)) {
                $where .= " AND ";
            }
            
            switch ($filter) {
                case 'deposits':
                    $where .= "t.transaction_type = 'deposit'";
                    break;
                case 'withdrawals':
                    $where .= "t.transaction_type = 'withdrawal'";
                    break;
                case 'transfers':
                    $where .= "t.transaction_type = 'transfer'";
                    break;
                case 'last24h':
                    $where .= "t.created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
                    break;
                case 'lastweek':
                    $where .= "t.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)";
                    break;
            }
        }
        
        // Prepare the query
        $query = "SELECT t.*, a.account_number, u.username 
                 FROM " . DB_PREFIX . "transactions t
                 JOIN " . DB_PREFIX . "accounts a ON t.account_id = a.id
                 JOIN " . DB_PREFIX . "users u ON a.user_id = u.id";
                 
        $countQuery = "SELECT COUNT(*) as total 
                      FROM " . DB_PREFIX . "transactions t
                      JOIN " . DB_PREFIX . "accounts a ON t.account_id = a.id
                      JOIN " . DB_PREFIX . "users u ON a.user_id = u.id";
        
        if (!empty($where)) {
            $query .= " WHERE " . $where;
            $countQuery .= " WHERE " . $where;
        }
        
        $query .= " ORDER BY t.created_at DESC
                   LIMIT " . ITEMS_PER_PAGE . "
                   OFFSET " . ($page - 1) * ITEMS_PER_PAGE;
        
        // Execute queries
        $transactions = $this->transactionModel->query($query, $params);
        $totalResult = $this->transactionModel->query($countQuery, $params, false);
        $total = $totalResult ? $totalResult['total'] : 0;
        
        // Calculate total pages
        $totalPages = ceil($total / ITEMS_PER_PAGE);
        
        // Log access
        Logger::access("Admin transactions page accessed", [
            'user_id' => $_SESSION['user_id'],
            'ip' => get_client_ip(),
            'page' => $page,
            'search' => $search,
            'filter' => $filter
        ]);
        
        // Render transactions page
        $this->render('admin/transactions', [
            'title' => 'Transaction Management',
            'transactions' => $transactions,
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'filter' => $filter,
            'total' => $total
        ]);
    }
    
    /**
     * System settings
     */
    public function settings()
    {
        // Handle security level updates
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $this->input('action');
            
            switch ($action) {
                case 'update_security_levels':
                    $levels = $this->input('security_levels', []);
                    $success = true;
                    
                    foreach ($levels as $vulnerability => $level) {
                        if (!$this->securityLevelModel->setLevel($vulnerability, $level, $_SESSION['user_id'])) {
                            $success = false;
                        }
                    }
                    
                    if ($success) {
                        $this->setFlash('success', 'Security levels updated successfully');
                    } else {
                        $this->setFlash('error', 'Failed to update security levels');
                    }
                    break;
                    
                case 'reset_security_levels':
                    if ($this->securityLevelModel->resetAll($_SESSION['user_id'])) {
                        $this->setFlash('success', 'Security levels reset to default');
                    } else {
                        $this->setFlash('error', 'Failed to reset security levels');
                    }
                    break;
            }
            
            $this->redirect('/admin/settings');
            return;
        }
        
        // Get security levels
        $securityLevels = $this->securityLevelModel->getAllLevels();
        
        // Log access
        Logger::access("Admin settings page accessed", [
            'user_id' => $_SESSION['user_id'],
            'ip' => get_client_ip()
        ]);
        
        // Render settings page
        $this->render('admin/settings', [
            'title' => 'System Settings',
            'securityLevels' => $securityLevels
        ]);
    }
}
