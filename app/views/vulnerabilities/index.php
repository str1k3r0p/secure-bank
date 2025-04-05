<?php
/**
 * Banking DVWA Project
 * Vulnerabilities Overview
 */
?>

<div class="vulnerabilities-overview">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="cyber-card">
                <div class="cyber-card-header">
                    <h3>Security Vulnerability Demonstrations</h3>
                </div>
                <div class="cyber-card-body">
                    <p>This section contains demonstrations of common web application security vulnerabilities. Each vulnerability is implemented with three different security levels to show the progression from insecure to secure code.</p>
                    
                    <div class="alert alert-warning">
                        <h5 class="alert-heading">Educational Purpose Only</h5>
                        <p>These vulnerabilities are intentionally implemented for educational purposes. In a real application, you should always use the most secure coding practices to protect your users and data.</p>
                    </div>
                    
                    <!-- Security Levels Configuration -->
                    <div class="security-config-container mb-4">
                        <h4>Security Level Configuration</h4>
                        <p>You can configure the security level for each vulnerability independently:</p>
                        
                        <form method="post" action="<?php echo APP_URL; ?>/vulnerabilities/set-security-level" class="mb-3">
                            <?php echo $this->csrfField(); ?>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered security-level-table">
                                    <thead>
                                        <tr>
                                            <th>Vulnerability</th>
                                            <th>Current Level</th>
                                            <th>Change Level</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($security_levels as $vulnerability => $level): ?>
                                            <tr>
                                                <td><?php echo ucwords(str_replace('_', ' ', $vulnerability)); ?></td>
                                                <td>
                                                    <span class="security-badge security-badge-<?php echo $level; ?>">
                                                        <?php echo ucfirst($level); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <select name="security_levels[<?php echo $vulnerability; ?>]" class="form-control security-level-select security-level-<?php echo $level; ?>">
                                                            <option value="low" <?php echo $level === 'low' ? 'selected' : ''; ?>>Low</option>
                                                            <option value="medium" <?php echo $level === 'medium' ? 'selected' : ''; ?>>Medium</option>
                                                            <option value="high" <?php echo $level === 'high' ? 'selected' : ''; ?>>High</option>
                                                        </select>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <button type="submit" name="update_levels" class="btn cyber-btn">Update Security Levels</button>
                                <button type="submit" name="reset_levels" class="btn cyber-btn-outline" onclick="return confirm('Reset all security levels to default?')">Reset to Default</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Vulnerabilities List -->
    <div class="row">
        <!-- Brute Force -->
        <div class="col-md-6 mb-4">
            <div class="cyber-card vulnerability-card">
                <div class="cyber-card-header">
                    <h3>Brute Force</h3>
                </div>
                <div class="cyber-card-body">
                    <p>Brute force attacks involve systematically attempting all possible combinations of credentials until finding the correct one. This vulnerability demonstrates different levels of protection against automated login attempts.</p>
                    
                    <div class="security-level-indicator mb-3">
                        <div class="level-label">Security Level:</div>
                        <div class="level-badge security-badge-<?php echo $security_levels['brute_force']; ?>">
                            <?php echo ucfirst($security_levels['brute_force']); ?>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?php echo APP_URL; ?>/vulnerabilities/brute-force" class="btn cyber-btn-sm">Explore Vulnerability</a>
                        <a href="<?php echo APP_URL; ?>/vulnerabilities/source?vulnerability=brute_force&level=<?php echo $security_levels['brute_force']; ?>" class="btn cyber-btn-outline-sm">View Source</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Command Injection -->
        <div class="col-md-6 mb-4">
            <div class="cyber-card vulnerability-card">
                <div class="cyber-card-header">
                    <h3>Command Injection</h3>
                </div>
                <div class="cyber-card-body">
                    <p>Command Injection vulnerabilities allow attackers to execute system commands on the host through a vulnerable application. This demonstrates how user input should be properly validated before being used in system commands.</p>
                    
                    <div class="security-level-indicator mb-3">
                        <div class="level-label">Security Level:</div>
                        <div class="level-badge security-badge-<?php echo $security_levels['cmd_injection']; ?>">
                            <?php echo ucfirst($security_levels['cmd_injection']); ?>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?php echo APP_URL; ?>/vulnerabilities/cmd-injection" class="btn cyber-btn-sm">Explore Vulnerability</a>
                        <a href="<?php echo APP_URL; ?>/vulnerabilities/source?vulnerability=cmd_injection&level=<?php echo $security_levels['cmd_injection']; ?>" class="btn cyber-btn-outline-sm">View Source</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- SQL Injection -->
        <div class="col-md-6 mb-4">
            <div class="cyber-card vulnerability-card">
                <div class="cyber-card-header">
                    <h3>SQL Injection</h3>
                </div>
                <div class="cyber-card-body">
                    <p>SQL Injection vulnerabilities allow attackers to manipulate database queries by injecting malicious SQL code. This demonstrates the importance of parameterized queries and proper input validation.</p>
                    
                    <div class="security-level-indicator mb-3">
                        <div class="level-label">Security Level:</div>
                        <div class="level-badge security-badge-<?php echo $security_levels['sql_injection']; ?>">
                            <?php echo ucfirst($security_levels['sql_injection']); ?>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?php echo APP_URL; ?>/vulnerabilities/sql-injection" class="btn cyber-btn-sm">Explore Vulnerability</a>
                        <a href="<?php echo APP_URL; ?>/vulnerabilities/source?vulnerability=sql_injection&level=<?php echo $security_levels['sql_injection']; ?>" class="btn cyber-btn-outline-sm">View Source</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Directory Traversal -->
        <div class="col-md-6 mb-4">
            <div class="cyber-card vulnerability-card">
                <div class="cyber-card-header">
                    <h3>Directory Traversal</h3>
                </div>
                <div class="cyber-card-body">
                    <p>Directory Traversal (Path Traversal) vulnerabilities allow attackers to access files and directories stored outside the web root folder. This demonstrates the importance of proper file path validation.</p>
                    
                    <div class="security-level-indicator mb-3">
                        <div class="level-label">Security Level:</div>
                        <div class="level-badge security-badge-<?php echo $security_levels['directory_traversal']; ?>">
                            <?php echo ucfirst($security_levels['directory_traversal']); ?>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?php echo APP_URL; ?>/vulnerabilities/directory-traversal" class="btn cyber-btn-sm">Explore Vulnerability</a>
                        <a href="<?php echo APP_URL; ?>/vulnerabilities/source?vulnerability=directory_traversal&level=<?php echo $security_levels['directory_traversal']; ?>" class="btn cyber-btn-outline-sm">View Source</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- XSS -->
        <div class="col-md-6 mb-4">
            <div class="cyber-card vulnerability-card">
                <div class="cyber-card-header">
                    <h3>Cross-Site Scripting (XSS)</h3>
                </div>
                <div class="cyber-card-body">
                    <p>Cross-Site Scripting vulnerabilities allow attackers to inject malicious client-side scripts into web pages viewed by other users. This demonstrates proper output encoding and content security policies.</p>
                    
                    <div class="security-level-indicator mb-3">
                        <div class="level-label">Security Level:</div>
                        <div class="level-badge security-badge-<?php echo $security_levels['xss']; ?>">
                            <?php echo ucfirst($security_levels['xss']); ?>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?php echo APP_URL; ?>/vulnerabilities/xss" class="btn cyber-btn-sm">Explore Vulnerability</a>
                        <a href="<?php echo APP_URL; ?>/vulnerabilities/source?vulnerability=xss&level=<?php echo $security_levels['xss']; ?>" class="btn cyber-btn-outline-sm">View Source</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.vulnerability-card {
    height: 100%;
    border: 1px solid #333;
    transition: all 0.3s ease;
}

.vulnerability-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0 15px rgba(0, 255, 255, 0.3);
    border-color: #00ffff;
}

.vulnerability-card .cyber-card-header {
    background: linear-gradient(90deg, rgba(10, 10, 40, 0.8), rgba(20, 20, 60, 0.8));
}

.vulnerability-card .cyber-card-header h3 {
    font-size: 1.4rem;
    color: #00ffff;
    margin: 0;
}

.security-level-indicator {
    display: flex;
    align-items: center;
    margin-top: 10px;
}

.level-label {
    margin-right: 10px;
    color: #ccc;
}

.security-badge {
    padding: 3px 10px;
    border-radius: 3px;
    font-size: 0.85rem;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.security-badge-low {
    background-color: rgba(255, 0, 0, 0.2);
    color: #ff4444;
    border: 1px solid #ff4444;
}

.security-badge-medium {
    background-color: rgba(255, 165, 0, 0.2);
    color: #ffaa00;
    border: 1px solid #ffaa00;
}

.security-badge-high {
    background-color: rgba(0, 255, 0, 0.2);
    color: #00cc00;
    border: 1px solid #00cc00;
}

.security-level-table {
    background-color: rgba(10, 10, 30, 0.5);
    color: #ccc;
}

.security-level-table th {
    background-color: rgba(0, 255, 255, 0.1);
    color: #00ffff;
    border-color: #333;
}

.security-level-table td {
    border-color: #333;
    vertical-align: middle;
}

.security-level-select {
    background-color: rgba(10, 10, 40, 0.8);
    color: #fff;
    border: 1px solid #444;
}

.security-level-select.security-level-low {
    border-color: #ff4444;
}

.security-level-select.security-level-medium {
    border-color: #ffaa00;
}

.security-level-select.security-level-high {
    border-color: #00cc00;
}

.security-config-container {
    background-color: rgba(10, 10, 40, 0.3);
    padding: 20px;
    border-radius: 5px;
    border-left: 4px solid #00ffff;
}

.security-config-container h4 {
    color: #00ffff;
    margin-bottom: 15px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add hover effects to vulnerability cards
    const vulnerabilityCards = document.querySelectorAll('.vulnerability-card');
    
    vulnerabilityCards.forEach(card => {
        card.addEventListener('mouseover', function() {
            // Add glitch effect to headers
            const header = this.querySelector('.cyber-card-header');
            if (header) {
                header.classList.add('glitch');
                
                setTimeout(() => {
                    header.classList.remove('glitch');
                }, 1000);
            }
        });
    });
    
    // Security level select styling
    const securitySelects = document.querySelectorAll('.security-level-select');
    
    securitySelects.forEach(select => {
        select.addEventListener('change', function() {
            // Remove existing security level classes
            this.classList.remove('security-level-low', 'security-level-medium', 'security-level-high');
            
            // Add class based on selected value
            this.classList.add('security-level-' + this.value);
        });
    });
});
</script>