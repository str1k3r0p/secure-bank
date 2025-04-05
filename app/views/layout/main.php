<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/cyberpunk-theme.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/animations.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/main.css">
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
            <div class="container">
                <nav class="navbar navbar-expand-lg navbar-dark">
                    <a class="navbar-brand cyber-logo" href="<?php echo APP_URL; ?>/">
                        <img src="<?php echo IMAGES_URL; ?>/logo.png" alt="<?php echo APP_NAME; ?>" height="40">
                        <span class="ml-2"><?php echo APP_NAME; ?></span>
                    </a>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarMain">
                        <ul class="navbar-nav mr-auto">
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo APP_URL; ?>/">Home</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo APP_URL; ?>/about">About</a>
                            </li>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo APP_URL; ?>/account/dashboard">Dashboard</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo APP_URL; ?>/transaction/history">Transactions</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo APP_URL; ?>/vulnerabilities">Vulnerabilities</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                        <ul class="navbar-nav ml-auto">
                            <?php if (isset($_SESSION['user_id'])): ?>
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
                            <?php else: ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo APP_URL; ?>/login">Login</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo APP_URL; ?>/register">Register</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </nav>
            </div>
        </header>
        
        <!-- Main content -->
        <main class="cyber-main">
            <div class="container mt-4">
                <?php if ($this->hasFlash()): ?>
                    <div class="row">
                        <div class="col-md-12">
                            <?php $this->flash(); ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php echo $content; ?>
            </div>
        </main>
        
        <!-- Footer -->
        <footer class="cyber-footer mt-5">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="text-neon"><?php echo APP_NAME; ?></h5>
                        <p>A cyberpunk-themed banking website with intentionally vulnerable implementations for educational purposes.</p>
                    </div>
                    <div class="col-md-3">
                        <h5 class="text-neon">Links</h5>
                        <ul class="list-unstyled">
                            <li><a href="<?php echo APP_URL; ?>/">Home</a></li>
                            <li><a href="<?php echo APP_URL; ?>/about">About</a></li>
                            <li><a href="<?php echo APP_URL; ?>/vulnerabilities">Vulnerabilities</a></li>
                        </ul>
                    </div>
                    <div class="col-md-3">
                        <h5 class="text-neon">Important</h5>
                        <ul class="list-unstyled">
                            <li><a href="#">Security Notice</a></li>
                            <li><a href="#">Privacy Policy</a></li>
                            <li><a href="#">Terms of Use</a></li>
                        </ul>
                    </div>
                </div>
                <hr class="cyber-hr">
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
    <?php if (isset($additional_js) && is_array($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?php echo JS_URL; ?>/<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>