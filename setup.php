<?php
/**
 * Banking DVWA Project
 * Setup Script
 * 
 * This script initializes the application by checking system requirements
 * and redirecting to the installation wizard.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define the application root path
define('ROOT_PATH', __DIR__);

// Check if the application is already installed
if (file_exists(ROOT_PATH . '/config/installed.php')) {
    header('Location: index.php');
    exit;
}

// Check if setup is being submitted
if (isset($_POST['setup'])) {
    // Redirect to install wizard
    header('Location: install/index.php');
    exit;
}

// Function to check if a directory is writable
function is_dir_writable($dir) {
    if (!file_exists($dir)) {
        return false;
    }
    return is_writable($dir);
}

// Function to check if a module is loaded
function is_module_loaded($module) {
    if (function_exists('apache_get_modules')) {
        return in_array($module, apache_get_modules());
    }
    return false;
}

// Perform system checks
$requirements = [
    'php_version' => [
        'name' => 'PHP Version (>= 7.4.0)',
        'status' => version_compare(PHP_VERSION, '7.4.0', '>='),
        'value' => PHP_VERSION
    ],
    'mod_rewrite' => [
        'name' => 'Apache mod_rewrite',
        'status' => is_module_loaded('mod_rewrite'),
        'value' => is_module_loaded('mod_rewrite') ? 'Enabled' : 'Disabled'
    ],
    'mysql' => [
        'name' => 'MySQL Support',
        'status' => extension_loaded('mysqli'),
        'value' => extension_loaded('mysqli') ? 'Available' : 'Not Available'
    ],
    'gd' => [
        'name' => 'GD Library',
        'status' => extension_loaded('gd'),
        'value' => extension_loaded('gd') ? 'Available' : 'Not Available'
    ],
    'config_writable' => [
        'name' => 'Config Directory Writable',
        'status' => is_dir_writable(ROOT_PATH . '/config'),
        'value' => is_dir_writable(ROOT_PATH . '/config') ? 'Writable' : 'Not Writable'
    ],
    'logs_writable' => [
        'name' => 'Logs Directory Writable',
        'status' => is_dir_writable(ROOT_PATH . '/logs'),
        'value' => is_dir_writable(ROOT_PATH . '/logs') ? 'Writable' : 'Not Writable'
    ]
];

// Check if all requirements are met
$all_requirements_met = true;
foreach ($requirements as $requirement) {
    if (!$requirement['status']) {
        $all_requirements_met = false;
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - Banking DVWA Project</title>
    <link rel="stylesheet" href="public/css/bootstrap.min.css">
    <style>
        body {
            background-color: #0a0a1e;
            color: #00ffff;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
        }
        .setup-container {
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
        .list-group-item {
            background-color: rgba(10, 10, 30, 0.5);
            color: #00ffff;
            border-color: #00ffff;
        }
        .badge-success {
            background-color: #00ff00;
            color: #000;
        }
        .badge-danger {
            background-color: #ff0000;
            color: #fff;
        }
        .cyberpunk-glitch {
            text-shadow: 0 0 5px #00ffff;
            animation: glitch 2s infinite;
        }
        @keyframes glitch {
            0% { text-shadow: 0 0 5px #00ffff; }
            25% { text-shadow: 0 0 5px #ff00ff; }
            50% { text-shadow: 0 0 5px #ffff00; }
            75% { text-shadow: 0 0 5px #00ff00; }
            100% { text-shadow: 0 0 5px #00ffff; }
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <h1 class="cyberpunk-glitch">Banking DVWA Project Setup</h1>
        <div class="alert alert-info">
            <strong>Welcome!</strong> This wizard will help you set up the Banking DVWA Project application.
        </div>
        
        <h2>System Requirements</h2>
        <ul class="list-group mb-4">
            <?php foreach ($requirements as $requirement): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?php echo $requirement['name']; ?>
                <span class="badge <?php echo $requirement['status'] ? 'badge-success' : 'badge-danger'; ?>">
                    <?php echo $requirement['value']; ?>
                </span>
            </li>
            <?php endforeach; ?>
        </ul>
        
        <?php if ($all_requirements_met): ?>
            <form method="post" action="">
                <div class="text-center">
                    <button type="submit" name="setup" class="btn btn-primary btn-lg">Start Installation</button>
                </div>
            </form>
        <?php else: ?>
            <div class="alert alert-danger">
                <strong>Error!</strong> Your system does not meet the requirements for installing the application.
                Please fix the issues above and try again.
            </div>
        <?php endif; ?>
    </div>
    
    <script src="public/js/jquery.min.js"></script>
    <script src="public/js/bootstrap.min.js"></script>
</body>
</html>