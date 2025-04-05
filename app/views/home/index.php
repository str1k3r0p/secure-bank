<div class="cyber-hero">
    <div class="row">
        <div class="col-md-6">
            <h1 class="cyber-title" data-text="Welcome to CyberBank">Welcome to CyberBank</h1>
            <p class="lead cyber-subtitle">A banking platform with intentional security vulnerabilities for educational exploration.</p>
            <p>Discover common web application vulnerabilities in a controlled environment. Learn about security best practices and how to protect against common attacks.</p>
            
            <?php if (!$isLoggedIn): ?>
                <div class="mt-4">
                    <a href="<?php echo APP_URL; ?>/register" class="btn btn-primary cyber-btn mr-2">Create Account</a>
                    <a href="<?php echo APP_URL; ?>/login" class="btn btn-outline-primary cyber-btn-outline">Login</a>
                </div>
            <?php else: ?>
                <div class="mt-4">
                    <a href="<?php echo APP_URL; ?>/account/dashboard" class="btn btn-primary cyber-btn mr-2">Dashboard</a>
                    <a href="<?php echo APP_URL; ?>/vulnerabilities" class="btn btn-outline-primary cyber-btn-outline">Explore Vulnerabilities</a>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <div class="cyber-image-container">
                <img src="<?php echo IMAGES_URL; ?>/cyberpunk/hero-image.jpg" alt="Cyberpunk Banking" class="img-fluid cyber-image">
                <div class="cyber-image-glitch"></div>
            </div>
        </div>
    </div>
</div>

<div class="cyber-section mt-5">
    <h2 class="text-center cyber-heading">Featured Vulnerabilities</h2>
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="cyber-card">
                <div class="cyber-card-header">
                    <h3>SQL Injection</h3>
                </div>
                <div class="cyber-card-body">
                    <p>Learn how attackers can execute malicious SQL statements that control a database server behind a web application.</p>
                    <a href="<?php echo APP_URL; ?>/vulnerabilities/sql-injection" class="btn btn-sm btn-primary cyber-btn-sm mt-2">Explore</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="cyber-card">
                <div class="cyber-card-header">
                    <h3>Cross-Site Scripting</h3>
                </div>
                <div class="cyber-card-body">
                    <p>Discover how malicious scripts can be injected into otherwise benign and trusted websites.</p>
                    <a href="<?php echo APP_URL; ?>/vulnerabilities/xss" class="btn btn-sm btn-primary cyber-btn-sm mt-2">Explore</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="cyber-card">
                <div class="cyber-card-header">
                    <h3>Brute Force</h3>
                </div>
                <div class="cyber-card-body">
                    <p>See how attackers can systematically check all possible passwords until the correct one is found.</p>
                    <a href="<?php echo APP_URL; ?>/vulnerabilities/brute-force" class="btn btn-sm btn-primary cyber-btn-sm mt-2">Explore</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="cyber-section mt-5">
    <div class="row">
        <div class="col-md-6">
            <h2 class="cyber-heading">Educational Purpose</h2>
            <p>This platform is designed exclusively for educational purposes to help developers, security professionals, and students understand how web application vulnerabilities work and how to prevent them.</p>
            <p>All vulnerabilities are implemented in a controlled environment with different security levels to demonstrate both insecure code and proper security measures.</p>
        </div>
        <div class="col-md-6">
            <div class="cyber-alert">
                <h4 class="cyber-alert-title">Security Warning</h4>
                <p>The vulnerabilities on this platform are real. Do not use real personal data or financial information. This is a simulated banking environment for security education only.</p>
                <p>Never apply the vulnerable code examples in production environments. The secure implementations demonstrate the correct approach for real-world applications.</p>
            </div>
        </div>
    </div>
</div>