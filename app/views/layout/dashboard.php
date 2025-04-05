<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/cyberpunk-theme.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/dashboard.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/animations.css">
    <link rel="shortcut icon" href="<?php echo IMAGES_URL; ?>/favicon.ico" type="image/x-icon">
    <?php if (isset($additional_css) && is_array($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link rel="stylesheet" href="<?php echo CSS_URL; ?>/<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <div class="cyber-container">
        <!-- Glitch overlay for cyberpunk effect -->
        <div class="glitch-overlay"></div>
        
        <!-- Header -->
        <header class="cyber-header">
            <div class="container-fluid">
                <nav class="navbar navbar-expand-lg navbar-dark">
                    <a class="navbar-brand cyber-logo" href="<?php echo APP_URL; ?>/">
                        <img src="<?php echo IMAGES_URL; ?>/logo.png" alt="<?php echo APP_NAME; ?>" height="40">
                        <span class="ml-2"><?php echo APP_NAME; ?></span>
                    </a>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarDashboard" aria-controls="navbarDashboard" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarDashboard">
                        <ul class="navbar-nav mr-auto">
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo APP_URL; ?>/account/dashboard">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo APP_URL; ?>/transaction/new">New Transaction</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo APP_URL; ?>/transaction/history">Transaction History</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo APP_URL; ?>/vulnerabilities">Vulnerabilities</a>
                            </li>
                        </ul>
                        <ul class="navbar-nav ml-auto">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="<?php echo APP_URL; ?>/account/settings">Account Settings</a>
                                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                        <a class="dropdown-item" href="<?php echo APP_URL; ?>/admin/dashboard">Admin Panel</a>
                                    <?php endif; ?>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="<?php echo APP_URL; ?>/logout">Logout</a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </header>
        
        <div class="cyber-dashboard">
            <div class="container-fluid">
                <div class="row">
                    <!-- Sidebar -->
                    <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                        <div class="sidebar-sticky pt-3">
                            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-neon">
                                <span>Banking</span>
                            </h6>
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo APP_URL; ?>/account/dashboard">
                                        <span data-feather="home"></span>
                                        Dashboard
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo APP_URL; ?>/transaction/new?type=deposit">
                                        <span data-feather="plus-circle"></span>
                                        Deposit
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo APP_URL; ?>/transaction/new?type=withdrawal">
                                        <span data-feather="minus-circle"></span>
                                        Withdraw
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo APP_URL; ?>/transaction/new?type=transfer">
                                        <span data-feather="refresh-cw"></span>
                                        Transfer
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo APP_URL; ?>/transaction/history">
                                        <span data-feather="list"></span>
                                        Transactions
                                    </a>
                                </li>
                            </ul>
                            
                            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-neon">
                                <span>Vulnerabilities</span>
                            </h6>
                            <ul class="nav flex-column mb-2">
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo APP_URL; ?>/vulnerabilities/brute-force">
                                        <span data-feather="key"></span>
                                        Brute Force
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo APP_URL; ?>/vulnerabilities/cmd-injection">
                                        <span data-feather="terminal"></span>
                                        Command Injection
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo APP_URL; ?>/vulnerabilities/sql-injection">
                                        <span data-feather="database"></span>
                                        SQL Injection
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo APP_URL; ?>/vulnerabilities/directory-traversal">
                                        <span data-feather="folder"></span>
                                        Directory Traversal
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo APP_URL; ?>/vulnerabilities/xss">
                                        <span data-feather="code"></span>
                                        XSS
                                    </a>
                                </li>
                            </ul>
                            
                            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-neon">
                                <span>Settings</span>
                            </h6>
                            <ul class="nav flex-column mb-2">
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo APP_URL; ?>/account/settings">
                                        <span data-feather="settings"></span>
                                        Account Settings
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo APP_URL; ?>/logout">
                                        <span data-feather="log-out"></span>
                                        Logout
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </nav>
                    
                    <!-- Main content -->
                    <main class="col-md-9 ml-sm-auto col-lg-10 px-md-4 cyber-main pb-5">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                            <h1 class="h2"><?php echo isset($title) ? $title : 'Dashboard'; ?></h1>
                            <div class="btn-toolbar mb-2 mb-md-0">
                                <div class="btn-group mr-2">
                                    <a href="<?php echo APP_URL; ?>/transaction/new" class="btn btn-sm cyber-btn">New Transaction</a>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($this->hasFlash()): ?>
                            <div class="row">
                                <div class="col-12">
                                    <?php $this->flash(); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php echo $content; ?>
                    </main>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <footer class="cyber-footer mt-5">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. For educational purposes only.</p>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    
    <!-- JavaScript -->
    <script src="<?php echo JS_URL; ?>/jquery.min.js"></script>
    <script src="<?php echo JS_URL; ?>/bootstrap.min.js"></script>
    <script src="<?php echo JS_URL; ?>/animations.js"></script>
    <script src="<?php echo JS_URL; ?>/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script>
        // Initialize Feather icons
        feather.replace();
    </script>
    <?php if (isset($additional_js) && is_array($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?php echo JS_URL; ?>/<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>