<?php
/**
 * Banking DVWA Project
 * XSS (Cross-Site Scripting) Vulnerability View
 */
?>

<div class="vulnerability-container">
    <div class="vulnerability-description mb-4">
        <h2>Cross-Site Scripting (XSS) Vulnerability</h2>
        <p><?php echo $vulnerability->getDescription(); ?></p>
        
        <div class="alert alert-danger">
            <h5 class="alert-heading">Security Impact</h5>
            <p>XSS vulnerabilities can allow attackers to:</p>
            <ul>
                <li>Steal user session cookies and hijack user accounts</li>
                <li>Capture keystrokes and steal sensitive information</li>
                <li>Deface websites or insert unwanted content</li>
                <li>Redirect users to malicious websites</li>
                <li>Perform actions on behalf of the user without their knowledge</li>
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
                    <p>At the <strong>low</strong> security level, the application directly outputs user input without any validation or sanitization, making it highly vulnerable to XSS attacks.</p>
                    <pre><code class="language-php">// User input is directly inserted into HTML
echo '<div>' . $message . '</div>';</code></pre>
                <?php elseif ($security_level === 'medium'): ?>
                    <p>At the <strong>medium</strong> security level, the application attempts to filter out some dangerous HTML tags and attributes, but these measures can still be bypassed.</p>
                    <pre><code class="language-php">// Basic filtering of some dangerous elements
$filtered = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $input);
$filtered = preg_replace('/on\w+\s*=\s*"[^"]*"/i', '', $filtered);
echo '<div>' . $filtered . '</div>';</code></pre>
                <?php else: ?>
                    <p>At the <strong>high</strong> security level, the application properly sanitizes all user input and implements content security policies to prevent XSS attacks.</p>
                    <pre><code class="language-php">// Proper encoding of all output
echo '<div>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</div>';</code></pre>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="vulnerability-demo mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Message Board Demo</h5>
            </div>
            <div class="card-body">
                <p>This is a simple message board where users can post messages. Try to inject JavaScript code that executes when the page loads or when specific actions are performed.</p>
                
                <form method="post" action="" class="mb-4">
                    <?php echo $this->csrfField(); ?>
                    <div class="form-group">
                        <label for="message">Post a message:</label>
                        <textarea class="form-control" id="message" name="message" rows="3" placeholder="Enter your message here"></textarea>
                    </div>
                    <button type="submit" class="btn cyber-btn">Post Message</button>
                </form>
                
                <?php if (isset($result) && isset($result['success'])): ?>
                    <?php if (!$result['success']): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($result['error']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($result['original_message']) && isset($result['filtered_message'])): ?>
                        <div class="alert alert-info">
                            <strong>Input was filtered:</strong>
                            <div class="mt-2">
                                <strong>Original:</strong> <pre class="d-inline"><?php echo htmlspecialchars($result['original_message']); ?></pre>
                            </div>
                            <div class="mt-2">
                                <strong>Filtered:</strong> <pre class="d-inline"><?php echo htmlspecialchars($result['filtered_message']); ?></pre>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <h6 class="mt-4">Message Board:</h6>
                <div class="message-board">
                    <?php if (empty($vulnerability->getMessages())): ?>
                        <div class="alert alert-info">No messages yet.</div>
                    <?php else: ?>
                        <?php foreach ($vulnerability->getMessages() as $msg): ?>
                            <?php echo $vulnerability->renderMessage($msg); ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="vulnerability-examples mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Common XSS Attack Vectors</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Attack Type</th>
                                <th>Description</th>
                                <th>Example</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Basic Script Injection</td>
                                <td>Inserting a script tag that executes when the page loads</td>
                                <td><code>&lt;script&gt;alert('XSS')&lt;/script&gt;</code></td>
                            </tr>
                            <tr>
                                <td>Event Handler</td>
                                <td>Using HTML attributes that execute JavaScript</td>
                                <td><code>&lt;img src="x" onerror="alert('XSS')"&gt;</code></td>
                            </tr>
                            <tr>
                                <td>JavaScript URL</td>
                                <td>Using JavaScript protocol in links</td>
                                <td><code>&lt;a href="javascript:alert('XSS')"&gt;Click me&lt;/a&gt;</code></td>
                            </tr>
                            <tr>
                                <td>DOM-Based</td>
                                <td>Exploiting JavaScript that inserts user input into the DOM</td>
                                <td><code>location.hash</code> manipulation</td>
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
                        <h6 class="text-success">Output Encoding</h6>
                        <p>Always encode user input before outputting it to the page using functions like <code>htmlspecialchars()</code> in PHP.</p>
                        <pre><code class="language-php">echo htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');</code></pre>
                    </li>
                    <li class="list-group-item">
                        <h6 class="text-success">Content Security Policy (CSP)</h6>
                        <p>Implement CSP headers to restrict the sources of executable scripts.</p>
                        <pre><code>Content-Security-Policy: default-src 'self';</code></pre>
                    </li>
                    <li class="list-group-item">
                        <h6 class="text-success">Input Validation</h6>
                        <p>Validate all input according to the expected format and reject invalid input.</p>
                    </li>
                    <li class="list-group-item">
                        <h6 class="text-success">Use Modern Frameworks</h6>
                        <p>Modern frameworks like React, Angular, and Vue automatically escape output.</p>
                    </li>
                    <li class="list-group-item">
                        <h6 class="text-success">X-XSS-Protection Header</h6>
                        <p>Enable the browser's built-in XSS filter.</p>
                        <pre><code>X-XSS-Protection: 1; mode=block</code></pre>
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

<style>
.message-container {
    border: 1px solid #2a2a40;
    border-radius: 5px;
    margin-bottom: 15px;
    background-color: #1a1a35;
}

.message-header {
    padding: 8px 12px;
    background-color: #2a2a40;
    display: flex;
    justify-content: space-between;
    border-top-left-radius: 5px;
    border-top-right-radius: 5px;
}

.message-user {
    font-weight: bold;
    color: #00ffff;
}

.message-time {
    color: #999;
    font-size: 0.9em;
}

.message-content {
    padding: 12px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Highlight code blocks
    document.querySelectorAll('pre code').forEach((block) => {
        hljs.highlightBlock(block);
    });
});
</script>