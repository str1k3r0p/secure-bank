<?php
/**
 * Banking DVWA Project
 * Security Unit Tests
 * 
 * This file contains unit tests for security-related functionality.
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Core\Security;

class SecurityTest extends TestCase
{
    /**
     * Test password hashing and verification
     */
    public function testPasswordHashing()
    {
        $password = 'password123';
        $hash = Security::hashPassword($password);
        
        // Hash should not match original password
        $this->assertNotEquals($password, $hash);
        
        // Verification should succeed with correct password
        $this->assertTrue(Security::verifyPassword($password, $hash));
        
        // Verification should fail with incorrect password
        $this->assertFalse(Security::verifyPassword('wrongpassword', $hash));
    }
    
    /**
     * Test CSRF token generation and verification
     */
    public function testCsrfTokens()
    {
        // Generate token
        $token = Security::generateToken(CSRF_TOKEN_LENGTH);
        
        // Token should match the expected length
        $this->assertEquals(CSRF_TOKEN_LENGTH, strlen($token));
        
        // Token should only contain hexadecimal characters
        $this->assertMatchesRegularExpression('/^[a-f0-9]+$/', $token);
    }
    
    /**
     * Test input sanitization
     */
    public function testSanitizeInput()
    {
        $input = '<script>alert("XSS");</script>';
        $sanitized = Security::sanitize($input);
        
        // Sanitized input should not contain the original script
        $this->assertNotEquals($input, $sanitized);
        
        // Sanitized input should have HTML entities encoded
        $this->assertStringContainsString('&lt;script&gt;', $sanitized);
    }
    
    /**
     * Test security level functionality
     */
    public function testSecurityLevels()
    {
        // Test default security level
        $this->assertEquals(DEFAULT_SECURITY_LEVEL, Security::getSecurityLevel('unknown_vulnerability'));
        
        // Test setting and getting security level
        $vulnerability = 'test_vulnerability';
        $level = 'medium';
        
        Security::setSecurityLevel($vulnerability, $level);
        $this->assertEquals($level, Security::getSecurityLevel($vulnerability));
        
        // Test security level validation
        $this->expectException(\InvalidArgumentException::class);
        Security::setSecurityLevel($vulnerability, 'invalid_level');
    }
}
