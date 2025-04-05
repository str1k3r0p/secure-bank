<?php
/**
 * Banking DVWA Project
 * SQL Injection Vulnerability View
 */
?>

<div class="vulnerability-container">
    <div class="vulnerability-description mb-4">
        <h2>SQL Injection Vulnerability</h2>
        <p><?php echo $vulnerability->getDescription(); ?></p>
        
        <div class="alert alert-danger">
            <h5 class="alert-heading">Security Impact</h5>
            <p>SQL injection vulnerabilities can allow attackers to:</p>
            <ul>
                <li>Access, modify, or delete data in the database</li>
                <li>Bypass authentication and authorization mechanisms</li>
                <li>Extract sensitive information such as user credentials</li>
                <li>Execute administrative database operations</li>
                <li>In some cases, execute commands on the database server</li>
            </ul>
        </div>
    </div>
    
    <div class="security-info mb-4">
        <div class="card">
            <div class="card-header security-header security-<?php echo $security_level; ?>">
                <h5 class="mb-0">Current Security Level: <?php echo ucfirst($security_level); ?></h5>
            </div>
            <div class="card-body">
                <?php if ($security_level === 'low'): ?>
                    <p>At the <strong>low</strong> security level, the application directly embeds user input into SQL queries without any validation or sanitization, making it highly vulnerable to SQL injection attacks.</p>
                    <pre><code class="language-php">$query = "SELECT * FROM users WHERE username LIKE '%" . $search . "%'";</code></pre>
                <?php elseif ($security_level === 'medium'): ?>
                    <p>At the <strong>medium</strong> security level, the application attempts to filter out common SQL injection patterns and keywords, but these measures can still be bypassed by a determined attacker.</p>
                    <pre><code class="language-php">$search = str_replace(['union', 'select', '--'], '', $search);
$query = "SELECT * FROM users WHERE username LIKE '%" . $search . "%'";</code></pre>
                <?php else: ?>
                    <p>At the <strong>high</strong> security level, the application uses parameterized queries to properly separate user input from SQL code, effectively preventing SQL injection attacks.</p>
                    <pre><code class="language-php">$query = "SELECT * FROM users WHERE username LIKE :search";
$params = ['search' => '%' . $search . '%'];
$results = $db->fetchAll($query, $params);</code></pre>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="vulnerability-demo mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">User Search Demo</h5>
            </div>
            <div class="card-body">
                <form method="post" action="" class="mb-4">
                    <?php echo $this->csrfField(); ?>
                    <div class="form-group">
                        <label for="search">Search for users:</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Enter search term" 
                               value="<?php echo isset($input['search']) ? htmlspecialchars($input['search']) : ''; ?>">
                        <small class="form-text text-muted">
                            Search by username, first name, last name, or email.
                        </small>
                    </div>
                    <button type="submit" class="btn cyber-btn">Search</button>
                </form>
                
                <?php if (isset($result) && !empty($result)): ?>
                    <?php if (isset($result['filtered_input'])): ?>
                        <div class="alert alert-info">
                            <strong>Input was filtered:</strong> "<?php echo htmlspecialchars($input['search']); ?>" → "<?php echo htmlspecialchars($result['filtered_input']); ?>"
                        </div>
                    <?php endif; ?>
                    
                    <div class="query-container mb-3">
                        <h6>SQL Query:</h6>
                        <pre class="bg-dark text-light p-3 code-block"><code><?php echo htmlspecialchars($result['query']); ?></code></pre>
                    </div>
                    
                    <h6>Results:</h6>
                    <?php if (empty($result['results'])): ?>
                        <div class="alert alert-warning">No results found.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-dark">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($result['results'] as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                                            <td><?php echo htmlspecialchars($user['status']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="vulnerability-examples mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Common SQL Injection Examples</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Attack Vector</th>
                                <th>Description</th>
                                <th>Example</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Union-Based</td>
                                <td>Uses UNION operator to combine results from the original query with those from an injected query</td>
                                <td><code>' UNION SELECT 1,username,password,4,5,6,7 FROM users --</code></td>
                            </tr>
                            <tr>
                                <td>Error-Based</td>
                                <td>Exploits error messages to extract information from the database</td>
                                <td><code>' AND EXTRACTVALUE(1, CONCAT(0x7e, (SELECT version()), 0x7e)) --</code></td>
                            </tr>
                            <tr>
                                <td>Boolean-Based</td>
                                <td>Uses TRUE/FALSE conditions to extract data one bit at a time</td>
                                <td><code>' AND (SELECT SUBSTRING(username,1,1) FROM users WHERE id=1)='a</code></td>
                            </tr>
                            <tr>
                                <td>Time-Based</td>
                                <td>Uses time delays to extract information when no output is visible</td>
                                <td><code>' AND IF(SUBSTRING(username,1,1)='a',SLEEP(5),0) --</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-warning mt-3">
                    <strong>Note:</strong> These examples are for educational purposes only. Attempting these attacks on systems without permission is illegal.
                </div>
            </div>
        </div>
    </div>
    
    <div class="vulnerability-prevention mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Prevention Techniques</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <h6 class="text-success">Use Parameterized Queries / Prepared Statements</h6>
                        <p>The most effective way to prevent SQL injection is to separate SQL code from data using parameterized queries.</p>
                        <pre><code class="language-php">$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);</code></pre>
                    </li>
                    <li class="list-group-item">
                        <h6 class="text-success">Use ORM (Object-Relational Mapping) Libraries</h6>
                        <p>ORM libraries typically use parameterized queries internally and provide an additional layer of protection.</p>
                    </li>
                    <li class="list-group-item">
                        <h6 class="text-success">Input Validation</h6>
                        <p>Validate all input according to the expected data type and format.</p>
                    </li>
                    <li class="list-group-item">
                        <h6 class="text-success">Least Privilege</h6>
                        <p>Use database accounts with the minimum necessary privileges for the application.</p>
                    </li>
                    <li class="list-group-item">
                        <h6 class="text-success">Sanitize Database Output</h6>
                        <p>Encode or escape any data retrieved from the database before displaying it to users.</p>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="view-source-container text-center mb-4">
        <?php if (isset($vulnerability) && isset($security_level)): ?>
            <a href="<?php echo APP_URL; ?>/vulnerabilities/source?vulnerability=<?php echo $vulnerability->getType(); ?>&level=<?php echo $security_level; ?>" class="btn cyber-btn-outline" target="_blank">
                View Source Code
            </a>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Highlight code blocks
    document.querySelectorAll('pre code').forEach((block) => {
        hljs.highlightBlock(block);
    });
});
</script>