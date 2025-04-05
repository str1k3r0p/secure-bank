<?php
/**
 * Banking DVWA Project
 * User Flow Integration Tests
 * 
 * This file contains integration tests for user workflows.
 */

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

class UserFlowTest extends TestCase
{
    /**
     * @var string Base URL for testing
     */
    protected $baseUrl = 'http://localhost/bank_dvwa_project';
    
    /**
     * @var resource cURL handle
     */
    protected $curl;
    
    /**
     * @var array Cookies for maintaining session
     */
    protected $cookies = [];
    
    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        // Initialize cURL
        $this->curl = curl_init();
        
        // Set common cURL options
        curl_setopt_array($this->curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_COOKIEJAR => 'cookies.txt',
            CURLOPT_COOKIEFILE => 'cookies.txt',
            CURLOPT_USERAGENT => 'PHPUnit Integration Test'
        ]);
    }
    
    /**
     * Clean up after testing
     */
    protected function tearDown(): void
    {
        // Close cURL handle
        curl_close($this->curl);
        
        // Remove cookie file
        if (file_exists('cookies.txt')) {
            unlink('cookies.txt');
        }
    }
    
    /**
     * Helper method to send HTTP request
     * 
     * @param string $url URL to request
     * @param string $method HTTP method
     * @param array $data POST data
     * @return array Response data
     */
    protected function sendRequest($url, $method = 'GET', $data = [])
    {
        // Build full URL
        $url = $this->baseUrl . '/' . ltrim($url, '/');
        
        // Set URL
        curl_setopt($this->curl, CURLOPT_URL, $url);
        
        // Set method
        if ($method === 'POST') {
            curl_setopt($this->curl, CURLOPT_POST, true);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($data));
        } else {
            curl_setopt($this->curl, CURLOPT_HTTPGET, true);
        }
        
        // Execute request
        $response = curl_exec($this->curl);
        $httpCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        
        return [
            'code' => $httpCode,
            'body' => $response,
            'info' => curl_getinfo($this->curl)
        ];
    }
    
    /**
     * Helper method to extract CSRF token from response
     * 
     * @param string $response Response HTML
     * @return string|null CSRF token
     */
    protected function extractCsrfToken($response)
    {
        if (preg_match('/<input type="hidden" name="csrf_token" value="([^"]+)"/', $response, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    /**
     * Test user registration and login flow
     */
    public function testRegistrationAndLogin()
    {
        // Skip test if running in CI environment
        // This test requires a running application
        $this->markTestSkipped('This test requires a running application.');
        
        // 1. Load home page
        $homeResponse = $this->sendRequest('');
        $this->assertEquals(200, $homeResponse['code']);
        $this->assertStringContainsString('Welcome to', $homeResponse['body']);
        
        // 2. Navigate to registration page
        $regPageResponse = $this->sendRequest('register');
        $this->assertEquals(200, $regPageResponse['code']);
        
        // Extract CSRF token
        $csrfToken = $this->extractCsrfToken($regPageResponse['body']);
        $this->assertNotNull($csrfToken, 'CSRF token not found');
        
        // 3. Register a new user
        $username = 'testuser_' . time();
        $email = $username . '@example.com';
        $password = 'Password123!';
        
        $regResponse = $this->sendRequest('register', 'POST', [
            'csrf_token' => $csrfToken,
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'password_confirm' => $password,
            'first_name' => 'Test',
            'last_name' => 'User'
        ]);
        
        // Should redirect to login page
        $this->assertEquals(200, $regResponse['code']);
        $this->assertStringContainsString('login', $regResponse['info']['url']);
        
        // 4. Login with new user
        $loginPageResponse = $this->sendRequest('login');
        $csrfToken = $this->extractCsrfToken($loginPageResponse['body']);
        
        $loginResponse = $this->sendRequest('login', 'POST', [
            'csrf_token' => $csrfToken,
            'username' => $username,
            'password' => $password
        ]);
        
        // Should redirect to dashboard
        $this->assertEquals(200, $loginResponse['code']);
        $this->assertStringContainsString('dashboard', $loginResponse['info']['url']);
        $this->assertStringContainsString('Welcome', $loginResponse['body']);
        
        // 5. Check dashboard content
        $dashboardResponse = $this->sendRequest('account/dashboard');
        $this->assertEquals(200, $dashboardResponse['code']);
        $this->assertStringContainsString('Account Summary', $dashboardResponse['body']);
        
        // 6. Logout
        $logoutResponse = $this->sendRequest('logout');
        
        // Should redirect to login page
        $this->assertEquals(200, $logoutResponse['code']);
        $this->assertStringContainsString('login', $logoutResponse['info']['url']);
    }
    
    /**
     * Test transaction workflow
     */
    public function testTransactionFlow()
    {
        // Skip test if running in CI environment
        $this->markTestSkipped('This test requires a running application with test data.');
        
        // 1. Login first
        $loginPageResponse = $this->sendRequest('login');
        $csrfToken = $this->extractCsrfToken($loginPageResponse['body']);
        
        $loginResponse = $this->sendRequest('login', 'POST', [
            'csrf_token' => $csrfToken,
            'username' => 'testuser', // Use a pre-created test user
            'password' => 'Password123!'
        ]);
        
        // Verify login successful
        $this->assertEquals(200, $loginResponse['code']);
        $this->assertStringContainsString('dashboard', $loginResponse['info']['url']);
        
        // 2. Navigate to transaction page
        $transPageResponse = $this->sendRequest('transaction/new');
        $this->assertEquals(200, $transPageResponse['code']);
        $this->assertStringContainsString('New Transaction', $transPageResponse['body']);
        
        // Extract CSRF token and account ID
        $csrfToken = $this->extractCsrfToken($transPageResponse['body']);
        $this->assertNotNull($csrfToken, 'CSRF token not found');
        
        // Extract account ID from form (simplified, would need more robust parsing in real test)
        preg_match('/<option value="(\d+)"/', $transPageResponse['body'], $matches);
        $accountId = isset($matches[1]) ? $matches[1] : null;
        $this->assertNotNull($accountId, 'Account ID not found');
        
        // 3. Create a deposit transaction
        $transResponse = $this->sendRequest('transaction/process', 'POST', [
            'csrf_token' => $csrfToken,
            'transaction_type' => 'deposit',
            'account_id' => $accountId,
            'amount' => '100.00',
            'description' => 'Test deposit'
        ]);
        
        // Should redirect to confirmation page
        $this->assertEquals(200, $transResponse['code']);
        $this->assertStringContainsString('confirmation', $transResponse['info']['url']);
        $this->assertStringContainsString('Transaction Confirmation', $transResponse['body']);
        
        // 4. Check transaction history
        $historyResponse = $this->sendRequest('transaction/history');
        $this->assertEquals(200, $historyResponse['code']);
        $this->assertStringContainsString('Transaction History', $historyResponse['body']);
        $this->assertStringContainsString('Test deposit', $historyResponse['body']);
    }
    
    /**
     * Test vulnerability demo access
     */
    public function testVulnerabilityAccess()
    {
        // Skip test if running in CI environment
        $this->markTestSkipped('This test requires a running application.');
        
        // 1. Login first (required for vulnerability access)
        $loginPageResponse = $this->sendRequest('login');
        $csrfToken = $this->extractCsrfToken($loginPageResponse['body']);
        
        $loginResponse = $this->sendRequest('login', 'POST', [
            'csrf_token' => $csrfToken,
            'username' => 'testuser', // Use a pre-created test user
            'password' => 'Password123!'
        ]);
        
        // 2. Access vulnerability overview page
        $vulnResponse = $this->sendRequest('vulnerabilities');
        $this->assertEquals(200, $vulnResponse['code']);
        $this->assertStringContainsString('Vulnerability Demonstrations', $vulnResponse['body']);
        
        // 3. Access specific vulnerability pages
        $sqlResponse = $this->sendRequest('vulnerabilities/sql-injection');
        $this->assertEquals(200, $sqlResponse['code']);
        $this->assertStringContainsString('SQL Injection', $sqlResponse['body']);
        
        $xssResponse = $this->sendRequest('vulnerabilities/xss');
        $this->assertEquals(200, $xssResponse['code']);
        $this->assertStringContainsString('Cross-Site Scripting', $xssResponse['body']);
    }
}
