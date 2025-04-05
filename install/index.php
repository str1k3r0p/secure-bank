<?php
/**
 * Banking DVWA Project
 * Installation Wizard
 * 
 * This script handles the installation of the application.
 */

// Set the application root path
define('ROOT_PATH', dirname(__DIR__));

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if already installed
if (file_exists(ROOT_PATH . '/config/installed.php')) {
    header('Location: ' . '/bank_dvwa_project/');
    exit;
}

// Include the requirements checker
include_once 'requirements.php';

// Step handler
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = null;
$success = null;

// Process steps
switch ($step) {
    case 1: // System Requirements
        // Just display the requirements page
        break;
        
    case 2: // Database Configuration
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get database details
            $db_host = $_POST['db_host'] ?? 'localhost';
            $db_name = $_POST['db_name'] ?? 'bank_dvwa';
            $db_user = $_POST['db_user'] ?? 'root';
            $db_pass = $_POST['db_pass'] ?? '';
            $db_port = $_POST['db_port'] ?? '3306';
            
            // Store in session
            $_SESSION['db_config'] = [
                'host' => $db_host,
                'name' => $db_name,
                'user' => $db_user,
                'pass' => $db_pass,
                'port' => $db_port
            ];
            
            // Test database connection
            try {
                $dsn = "mysql:host={$db_host};port={$db_port}";
                $pdo = new PDO($dsn, $db_user, $db_pass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Check if database exists
                $stmt = $pdo->query("SHOW DATABASES LIKE '{$db_name}'");
                if ($stmt->rowCount() === 0) {
                    // Create database
                    $pdo->exec("CREATE DATABASE `{$db_name}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
                }
                
                // Switch to the database
                $pdo->exec("USE `{$db_name}`");
                
                // Store database config for next step
                $success = "Database connection successful";
                
                // Redirect to next step
                header('Location: index.php?step=3');
                exit;
            } catch (PDOException $e) {
                $error = "Database connection failed: " . $e->getMessage();
            }
        }
        break;
        
    case 3: // Database Setup
        if (!isset($_SESSION['db_config'])) {
            header('Location: index.php?step=2');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['run_setup'])) {
            $db_config = $_SESSION['db_config'];
            
            try {
                // Connect to database
                $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['name']}";
                $pdo = new PDO($dsn, $db_config['user'], $db_config['pass']);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Read schema SQL
                $schema_file = ROOT_PATH . '/database/setup/database.sql';
                $sample_data_file = ROOT_PATH . '/database/setup/sample_data.sql';
                
                if (!file_exists($schema_file)) {
                    throw new Exception("Database schema file not found: {$schema_file}");
                }
                
                if (!file_exists($sample_data_file)) {
                    throw new Exception("Sample data file not found: {$sample_data_file}");
                }
                
                // Execute schema SQL
                $schema_sql = file_get_contents($schema_file);
                $pdo->exec($schema_sql);
                
                // Execute sample data SQL
                $sample_data_sql = file_get_contents($sample_data_file);
                $pdo->exec($sample_data_sql);
                
                // Write database configuration file
                $config_file = ROOT_PATH . '/config/database.php';
                $config_content = <<<PHP
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
define('DB_HOST', '{$db_config['host']}');           // Database host
define('DB_NAME', '{$db_config['name']}');           // Database name
define('DB_USER', '{$db_config['user']}');                // Database username
define('DB_PASS', '{$db_config['pass']}');                    // Database password
define('DB_CHARSET', 'utf8mb4');          // Database charset
define('DB_PORT', '{$db_config['port']}');                // Database port
define('DB_PREFIX', 'bdv_');              // Table prefix

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
                if (!file_exists(dirname($config_file))) {
                    mkdir(dirname($config_file), 0755, true);
                }
                
                // Write the config file
                file_put_contents($config_file, $config_content);
                
                // Success
                $success = "Database setup completed successfully";
                
                // Redirect to next step
                header('Location: index.php?step=4');
                exit;
            } catch (Exception $e) {
                $error = "Database setup failed: " . $e->getMessage();
            }
        }
        break;
        
    case 4: // Finalize Installation
        if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['finalize'])) {
            try {
                // Create installed flag file
                $installed_file = ROOT_PATH . '/config/installed.php';
                $installed_content = <<<PHP
<?php
/**
 * Banking DVWA Project
 * Installation Flag
 * 
 * This file indicates that the application has been installed.
 */

define('INSTALLED', true);
define('INSTALLED_DATE', '" . date('Y-m-d H:i:s') . "');
PHP;

                // Write the installed flag file
                file_put_contents($installed_file, $installed_content);
                
                // Create required directories
                $directories = [
                    ROOT_PATH . '/logs',
                    ROOT_PATH . '/temp',
                    ROOT_PATH . '/temp/cache',
                    ROOT_PATH . '/temp/uploads',
                    ROOT_PATH . '/public/assets/demo/sample_statements'
                ];
                
                foreach ($directories as $dir) {
                    if (!file_exists($dir)) {
                        mkdir($dir, 0755, true);
                    }
                }
                
                // Create .htaccess files in sensitive directories
                $htaccess_content = "Order deny,allow\nDeny from all";
                file_put_contents(ROOT_PATH . '/logs/.htaccess', $htaccess_content);
                file_put_contents(ROOT_PATH . '/database/backups/.htaccess', $htaccess_content);
                
                // Success
                $success = "Installation completed successfully";
                
                // Clear session
                session_destroy();
                
                // Redirect to homepage after 3 seconds
                header('Refresh: 3; URL=' . '/bank_dvwa_project/');
            } catch (Exception $e) {
                $error = "Installation failed: " . $e->getMessage();
            }
        }
        break;
        
    default:
        header('Location: index.php?step=1');
        exit;
}

// Get requirements
$requirements = check_requirements();
$all_requirements_met = array_reduce($requirements, function($carry, $requirement) {
    return $carry && $requirement['status'];
}, true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Banking DVWA Project</title>
    <link rel="stylesheet" href="../public/css/bootstrap.min.css">
    <link rel="stylesheet" href="../public/css/cyberpunk-theme.css">
    <style>
        body {
            background-color: #0a0a1e;
            color: #00ffff;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
        }
        .install-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background-color: rgba(10, 10, 30, 0.8);
            border: 1px solid #00ffff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 255, 255, 0.3);
        }
        h1, h2 {
            color: #ff00ff;
            text-align: center;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        .step-indicator::before {
            content: "";
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #333;
            z-index: 1;
        }
        .step {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #333;
            color: #ccc;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            z-index: 2;
        }
        .step.active {
            background-color: #00ffff;
            color: #0a0a1e;
        }
        .step.completed {
            background-color: #00ff00;
            color: #0a0a1e;
        }
        .step-label {
            position: absolute;
            top: 40px;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
            color: #ccc;
            font-size: 0.8rem;
        }
        .btn-primary {
            background-color: #00ffff;
            border-color: #00ffff;
            color: #0a0a1e;
        }
        .btn-primary:hover {
            background-color: #ff00ff;
            border-color: #ff00ff;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <h1 class="mb-4">Banking DVWA Project Installation</h1>
        
        <!-- Step Indicator -->
        <div class="step-indicator mb-5">
            <div class="step <?php echo $step >= 1 ? 'active' : ''; ?> <?php echo $step > 1 ? 'completed' : ''; ?>">1
                <div class="step-label">Requirements</div>
            </div>
            <div class="step <?php echo $step >= 2 ? 'active' : ''; ?> <?php echo $step > 2 ? 'completed' : ''; ?>">2
                <div class="step-label">Database</div>
            </div>
            <div class="step <?php echo $step >= 3 ? 'active' : ''; ?> <?php echo $step > 3 ? 'completed' : ''; ?>">3
                <div class="step-label">Setup</div>
            </div>
            <div class="step <?php echo $step >= 4 ? 'active' : ''; ?>">4
                <div class="step-label">Complete</div>
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <!-- Step Content -->
        <?php if ($step === 1): // Requirements ?>
            <h2>System Requirements</h2>
            <p class="mb-4">Please ensure your system meets the following requirements before proceeding with the installation:</p>
            
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Requirement</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requirements as $requirement): ?>
                            <tr>
                                <td><?php echo $requirement['name']; ?></td>
                                <td>
                                    <?php if ($requirement['status']): ?>
                                        <span class="text-success">✓ <?php echo $requirement['value']; ?></span>
                                    <?php else: ?>
                                        <span class="text-danger">✗ <?php echo $requirement['value']; ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 text-center">
                <?php if ($all_requirements_met): ?>
                    <a href="index.php?step=2" class="btn btn-primary">Continue to Database Configuration</a>
                <?php else: ?>
                    <div class="alert alert-warning">
                        Please fix the requirements before continuing with the installation.
                    </div>
                <?php endif; ?>
            </div>
            
        <?php elseif ($step === 2): // Database Configuration ?>
            <h2>Database Configuration</h2>
            <p class="mb-4">Please provide your database connection details:</p>
            
            <form method="post" action="index.php?step=2">
                <div class="form-group mb-3">
                    <label for="db_host">Database Host</label>
                    <input type="text" class="form-control" id="db_host" name="db_host" value="localhost" required>
                </div>
                
                <div class="form-group mb-3">
                    <label for="db_name">Database Name</label>
                    <input type="text" class="form-control" id="db_name" name="db_name" value="bank_dvwa" required>
                </div>
                
                <div class="form-group mb-3">
                    <label for="db_user">Database Username</label>
                    <input type="text" class="form-control" id="db_user" name="db_user" value="root" required>
                </div>
                
                <div class="form-group mb-3">
                    <label for="db_pass">Database Password</label>
                    <input type="password" class="form-control" id="db_pass" name="db_pass" value="">
                </div>
                
                <div class="form-group mb-3">
                    <label for="db_port">Database Port</label>
                    <input type="text" class="form-control" id="db_port" name="db_port" value="3306" required>
                </div>
                
                <div class="alert alert-info">
                    <strong>Note:</strong> This will create a new database if it doesn't exist. Make sure the database user has sufficient privileges.
                </div>
                
                <div class="mt-4 text-center">
                    <button type="submit" class="btn btn-primary">Test Connection and Continue</button>
                </div>
            </form>
            
        <?php elseif ($step === 3): // Database Setup ?>
            <h2>Database Setup</h2>
            <p class="mb-4">Now we will set up the database schema and sample data.</p>
            
            <div class="alert alert-info">
                <strong>Note:</strong> This will create the necessary tables and insert sample data for the application.
            </div>
            
            <form method="post" action="index.php?step=3">
                <div class="mt-4 text-center">
                    <button type="submit" class="btn btn-primary">Set Up Database</button>
                </div>
            </form>
            
        <?php elseif ($step === 4): // Finalize Installation ?>
            <h2>Installation Complete</h2>