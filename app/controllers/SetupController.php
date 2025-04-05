<?php
/**
 * Banking DVWA Project
 * Setup Controller
 * 
 * Handles application setup and installation.
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Logger;
use App\Core\Database;
use App\Models\User;
use App\Models\SecurityLevel;

class SetupController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Setup index page
     */
    public function index()
    {
        // Check if application is already installed
        if (file_exists(ROOT_PATH . '/config/installed.php')) {
            $this->redirect('/');
            return;
        }
        
        // Render setup page
        $this->render('setup/index', [
            'title' => 'Install Application'
        ], 'minimal');
    }
    
    /**
     * System requirements check
     */
    public function requirements()
    {
        // Check if application is already installed
        if (file_exists(ROOT_PATH . '/config/installed.php')) {
            $this->redirect('/');
            return;
        }
        
        // Check requirements
        $requirements = $this->checkRequirements();
        $allRequirementsMet = array_reduce($requirements, function($carry, $requirement) {
            return $carry && $requirement['status'];
        }, true);
        
        // Render requirements page
        $this->render('setup/requirements', [
            'title' => 'System Requirements',
            'requirements' => $requirements,
            'allRequirementsMet' => $allRequirementsMet
        ], 'minimal');
    }
    
    /**
     * Database configuration
     */
    public function database()
    {
        // Check if application is already installed
        if (file_exists(ROOT_PATH . '/config/installed.php')) {
            $this->redirect('/');
            return;
        }
        
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get database details
            $dbHost = $this->input('db_host', 'localhost');
            $dbName = $this->input('db_name', 'bank_dvwa');
            $dbUser = $this->input('db_user', 'root');
            $dbPass = $this->input('db_pass', '');
            $dbPort = $this->input('db_port', '3306');
            $dbPrefix = $this->input('db_prefix', 'bdv_');
            
            // Validate required fields
            $errors = [];
            if (empty($dbHost)) {
                $errors['db_host'] = 'Database host is required';
            }
            if (empty($dbName)) {
                $errors['db_name'] = 'Database name is required';
            }
            if (empty($dbUser)) {
                $errors['db_user'] = 'Database username is required';
            }
            
            if (empty($errors)) {
                // Test database connection
                try {
                    $dsn = "mysql:host={$dbHost};port={$dbPort}";
                    $pdo = new \PDO($dsn, $dbUser, $dbPass);
                    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                    
                    // Check if database exists
                    $stmt = $pdo->query("SHOW DATABASES LIKE '{$dbName}'");
                    if ($stmt->rowCount() === 0) {
                        // Create database
                        $pdo->exec("CREATE DATABASE `{$dbName}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
                    }
                    
                    // Store database config in session
                    $_SESSION['setup_db_config'] = [
                        'host' => $dbHost,
                        'name' => $dbName,
                        'user' => $dbUser,
                        'pass' => $dbPass,
                        'port' => $dbPort,
                        'prefix' => $dbPrefix
                    ];
                    
                    // Redirect to next step
                    $this->setFlash('success', 'Database connection successful');
                    $this->redirect('/setup/schema');
                    return;
                } catch (\PDOException $e) {
                    $errors['db_connection'] = 'Database connection failed: ' . $e->getMessage();
                }
            }
            
            // If errors, render form with errors
            $this->render('setup/database', [
                'title' => 'Database Configuration',
                'errors' => $errors,
                'dbHost' => $dbHost,
                'dbName' => $dbName,
                'dbUser' => $dbUser,
                'dbPort' => $dbPort,
                'dbPrefix' => $dbPrefix
            ], 'minimal');
            return;
        }
        
        // Render database configuration page
        $this->render('setup/database', [
            'title' => 'Database Configuration'
        ], 'minimal');
    }
    
    /**
     * Database schema installation
     */
    public function schema()
    {
        // Check if application is already installed
        if (file_exists(ROOT_PATH . '/config/installed.php')) {
            $this->redirect('/');
            return;
        }
        
        // Check if database config exists in session
        if (!isset($_SESSION['setup_db_config'])) {
            $this->redirect('/setup/database');
            return;
        }
        
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['auto_install'])) {
            $dbConfig = $_SESSION['setup_db_config'];
            $includeSampleData = $this->input('include_sample_data', 'yes') === 'yes';
            
            try {
                // Connect to database
                $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['name']}";
                $pdo = new \PDO($dsn, $dbConfig['user'], $dbConfig['pass']);
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                
                // Read schema SQL
                $schemaFile = ROOT_PATH . '/database/setup/database.sql';
                if (!file_exists($schemaFile)) {
                    throw new \Exception("Database schema file not found: {$schemaFile}");
                }
                
                // Execute schema SQL
                $schemaSql = file_get_contents($schemaFile);
                // Replace table prefix if needed
                $schemaSql = str_replace('bdv_', $dbConfig['prefix'], $schemaSql);
                
                // Split SQL by semicolons to execute multiple statements
                $statements = array_filter(array_map('trim', explode(';', $schemaSql)));
                foreach ($statements as $statement) {
                    if (!empty($statement)) {
                        $pdo->exec($statement);
                    }
                }
                
                // Include sample data if requested
                if ($includeSampleData) {
                    $sampleDataFile = ROOT_PATH . '/database/setup/sample_data.sql';
                    if (!file_exists($sampleDataFile)) {
                        throw new \Exception("Sample data file not found: {$sampleDataFile}");
                    }
                    
                    $sampleDataSql = file_get_contents($sampleDataFile);
                    // Replace table prefix if needed
                    $sampleDataSql = str_replace('bdv_', $dbConfig['prefix'], $sampleDataSql);
                    
                    // Split SQL by semicolons to execute multiple statements
                    $statements = array_filter(array_map('trim', explode(';', $sampleDataSql)));
                    foreach ($statements as $statement) {
                        if (!empty($statement)) {
                            $pdo->exec($statement);
                        }
                    }
                }
                
                // Write database configuration file
                $configFile = ROOT_PATH . '/config/database.php';
                $configContent = <<<PHP
<?php
/**
 * Banking DVWA Project
 * Database Configuration
 * 
 * This file contains database connection settings.
 */

// Prevent direct access to this file
if (!defined('ROOT_PATH')) {
    die('Direct access to this file is not allowed.');
}

// Database configuration
define('DB_HOST', '{$dbConfig['host']}');           // Database host
define('DB_NAME', '{$dbConfig['name']}');           // Database name
define('DB_USER', '{$dbConfig['user']}');                // Database username
define('DB_PASS', '{$dbConfig['pass']}');                    // Database password
define('DB_CHARSET', 'utf8mb4');          // Database charset
define('DB_PORT', '{$dbConfig['port']}');                // Database port
define('DB_PREFIX', '{$dbConfig['prefix']}');              // Table prefix

// PDO connection options
define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
]);

// Connection string for PDO
define('DB_DSN', 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET . ';port=' . DB_PORT);
PHP;

                // Make sure the config directory exists
                if (!file_exists(dirname($configFile))) {
                    mkdir(dirname($configFile), 0755, true);
                }
                
                // Write the config file
                file_put_contents($configFile, $configContent);
                
                // Set success message
                $this->setFlash('success', 'Database schema installed successfully');
                
                // Redirect to next step
                $this->redirect('/setup/admin');
                return;
            } catch (\Exception $e) {
                $this->setFlash('error', 'Database schema installation failed: ' . $e->getMessage());
                $this->redirect('/setup/schema');
                return;
            }
        }
        
        // Render schema installation page
        $this->render('setup/schema', [
            'title' => 'Database Schema'
        ], 'minimal');
    }
    
    /**
     * Admin account setup
     */
    public function admin()
    {
        // Check if application is already installed
        if (file_exists(ROOT_PATH . '/config/installed.php')) {
            $this->redirect('/');
            return;
        }
        
        // Check if database config exists in session
        if (!isset($_SESSION['setup_db_config'])) {
            $this->redirect('/setup/database');
            return;
        }
        
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get admin account details
            $username = $this->input('username');
            $email = $this->input('email');
            $password = $this->input('password');
            $passwordConfirm = $this->input('password_confirm');
            $firstName = $this->input('first_name');
            $lastName = $this->input('last_name');
            
            // Validate form data
            $errors = $this->validate([
                'username' => 'required|alpha_num|min:3|max:50',
                'email' => 'required|email',
                'password' => 'required|min:8',
                'password_confirm' => 'required|same:password',
                'first_name' => 'required|alpha|max:50',
                'last_name' => 'required|alpha|max:50'
            ]);
            
            if (empty($errors)) {
                try {
                    // Connect to database using Config
                    require_once ROOT_PATH . '/config/database.php';
                    $db = Database::getInstance();
                    
                    // Create User model
                    $userModel = new User();
                    
                    // Check if username or email already exists
                    if ($userModel->usernameExists($username)) {
                        $errors['username'] = 'Username already exists';
                    }
                    
                    if ($userModel->emailExists($email)) {
                        $errors['email'] = 'Email already exists';
                    }
                    
                    if (empty($errors)) {
                        // Create admin user
                        $userId = $userModel->create([
                            'username' => $username,
                            'email' => $email,
                            'password' => $password,
                            'first_name' => $firstName,
                            'last_name' => $lastName,
                            'role' => ROLE_ADMIN,
                            'status' => 'active'
                        ]);
                        
                        if ($userId) {
                            // Store admin info in session
                            $_SESSION['setup_admin'] = [
                                'id' => $userId,
                                'username' => $username
                            ];
                            
                            // Set success message
                            $this->setFlash('success', 'Admin account created successfully');
                            
                            // Redirect to next step
                            $this->redirect('/setup/finalize');
                            return;
                        } else {
                            $this->setFlash('error', 'Failed to create admin account');
                        }
                    }
                } catch (\Exception $e) {
                    $this->setFlash('error', 'Admin account creation failed: ' . $e->getMessage());
                }
            }
            
            // If errors, render form with errors
            $this->render('setup/admin', [
                'title' => 'Admin Account Setup',
                'errors' => $errors,
                'username' => $username,
                'email' => $email,
                'firstName' => $firstName,
                'lastName' => $lastName
            ], 'minimal');
            return;
        }
        
        // Render admin account setup page
        $this->render('setup/admin', [
            'title' => 'Admin Account Setup'
        ], 'minimal');
    }
    
    /**
     * Finalize installation
     */
    public function finalize()
    {
        // Check if application is already installed
        if (file_exists(ROOT_PATH . '/config/installed.php')) {
            $this->redirect('/');
            return;
        }
        
        // Check if database config and admin account exist in session
        if (!isset($_SESSION['setup_db_config']) || !isset($_SESSION['setup_admin'])) {
            $this->redirect('/setup/database');
            return;
        }
        
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['auto_finalize'])) {
            try {
                // Create required directories
                $directories = [
                    ROOT_PATH . '/logs',
                    ROOT_PATH . '/temp',
                    ROOT_PATH . '/temp/cache',
                    ROOT_PATH . '/temp/uploads',
                    PUBLIC_PATH . '/assets/demo/sample_statements'
                ];
                
                foreach ($directories as $dir) {
                    if (!file_exists($dir)) {
                        if (!mkdir($dir, 0755, true)) {
                            throw new \Exception("Failed to create directory: {$dir}");
                        }
                    }
                }
                
                // Create .htaccess files in sensitive directories
                $htaccessContent = "Order deny,allow\nDeny from all";
                file_put_contents(ROOT_PATH . '/logs/.htaccess', $htaccessContent);
                file_put_contents(ROOT_PATH . '/config/.htaccess', $htaccessContent);
                
                // Create installed flag file
                $installedFile = ROOT_PATH . '/config/installed.php';
                $installedContent = <<<PHP
<?php
/**
 * Banking DVWA Project
 * Installation Flag
 * 
 * This file indicates that the application has been installed.
 */

define('INSTALLED', true);
define('INSTALLED_DATE', '" . date('Y-m-d H:i:s') . "');
define('INSTALLED_BY', '{$_SESSION['setup_admin']['username']}');
PHP;

                // Write the installed flag file
                file_put_contents($installedFile, $installedContent);
                
                // Log successful installation
                if (file_exists(ROOT_PATH . '/logs')) {
                    $logMessage = "Application installed successfully by {$_SESSION['setup_admin']['username']}";
                    $logFile = ROOT_PATH . '/logs/app.log';
                    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] [info] {$logMessage}\n", FILE_APPEND);
                }
                
                // Set success message
                $this->setFlash('success', 'Installation completed successfully');
                
                // Redirect to login page
                $this->redirect('/login');
                return;
            } catch (\Exception $e) {
                $this->setFlash('error', 'Installation failed: ' . $e->getMessage());
                $this->redirect('/setup/finalize');
                return;
            }
        }
        
        // Render finalize page
        $this->render('setup/finalize', [
            'title' => 'Finalize Installation',
            'dbConfig' => $_SESSION['setup_db_config'],
            'adminUser' => $_SESSION['setup_admin']
        ], 'minimal');
    }
    
    /**
     * Check system requirements
     * 
     * @return array Requirements with status
     */
    private function checkRequirements()
    {
        return [
            [
                'name' => 'PHP Version (>= 7.4.0)',
                'status' => version_compare(PHP_VERSION, '7.4.0', '>='),
                'value' => PHP_VERSION
            ],
            [
                'name' => 'MySQLi Extension',
                'status' => extension_loaded('mysqli'),
                'value' => extension_loaded('mysqli') ? 'Available' : 'Not Available'
            ],
            [
                'name' => 'PDO Extension',
                'status' => extension_loaded('pdo'),
                'value' => extension_loaded('pdo') ? 'Available' : 'Not Available'
            ],
            [
                'name' => 'PDO MySQL Extension',
                'status' => extension_loaded('pdo_mysql'),
                'value' => extension_loaded('pdo_mysql') ? 'Available' : 'Not Available'
            ],
            [
                'name' => 'GD Extension',
                'status' => extension_loaded('gd'),
                'value' => extension_loaded('gd') ? 'Available' : 'Not Available'
            ],
            [
                'name' => 'Apache mod_rewrite',
                'status' => $this->isModRewriteEnabled(),
                'value' => $this->isModRewriteEnabled() ? 'Enabled' : 'Not Enabled'
            ],
            [
                'name' => 'Config Directory Writable',
                'status' => is_writable(ROOT_PATH . '/config') || $this->canCreateDirectory(ROOT_PATH . '/config'),
                'value' => (is_writable(ROOT_PATH . '/config') || $this->canCreateDirectory(ROOT_PATH . '/config')) ? 'Writable' : 'Not Writable'
            ],
            [
                'name' => 'Logs Directory Writable',
                'status' => is_writable(ROOT_PATH . '/logs') || $this->canCreateDirectory(ROOT_PATH . '/logs'),
                'value' => (is_writable(ROOT_PATH . '/logs') || $this->canCreateDirectory(ROOT_PATH . '/logs')) ? 'Writable' : 'Not Writable'
            ]
