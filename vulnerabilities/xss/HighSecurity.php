<?php
/**
 * Banking DVWA Project
 * XSS (Cross-Site Scripting) - High Security Implementation
 * 
 * This class implements the XSS vulnerability with high security.
 * It properly sanitizes all user input and uses content security policies.
 */

namespace Vulnerabilities\xss;

use App\Core\Logger;

class HighSecurity extends XSS
{
    /**
     * Execute the vulnerability
     * 
     * @param array $input Input data
     * @return array Message posting results
     */
    public function execute($input)
    {
        // Check if message is provided
        if (!isset($input['message']) || empty($input['message'])) {
            return [
                'success' => false,
                'error' => 'Please enter a message'
            ];
        }
        
        // Get message from input
        $message = $input['message'];
        
        // Get user from session
        $user = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
        
        // SECURE: Properly sanitize user input
        $sanitized_message = $this->sanitizeInput($message);
        
        // Log original vs. sanitized message
        if ($message !== $sanitized_message) {
            Logger::security("XSS attempt sanitized (HIGH)", [
                'original' => $message,
                'sanitized' => $sanitized_message,
                'ip' => get_client_ip(),
                'user_id' => $_SESSION['user_id'] ?? 'guest'
            ]);
        }
        
        // Add the sanitized message
        $this->addMessage($user, $sanitized_message);
        
        return [
            'success' => true,
            'messages' => $this->getMessages()
        ];
    }
    
    /**
     * Sanitize user input
     * 
     * @param string $input User input
     * @return string Sanitized input
     */
    private function sanitizeInput($input)
    {
        // Convert special characters to HTML entities
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Render a message for display
     * 
     * @param array $message Message data
     * @return string Rendered HTML
     */
    public function renderMessage($message)
    {
        $html = '<div class="message-container">';
        $html .= '<div class="message-header">';
        $html .= '<span class="message-user">' . htmlspecialchars($message['user'], ENT_QUOTES, 'UTF-8') . '</span>';
        $html .= '<span class="message-time">' . date('M j, Y g:i A', $message['timestamp']) . '</span>';
        $html .= '</div>';
        $html .= '<div class="message-content">' . htmlspecialchars($message['message'], ENT_QUOTES, 'UTF-8') . '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Get additional security headers
     * 
     * @return array Additional security headers
     */
    public function getSecurityHeaders()
    {
        return [
            'Content-Security-Policy' => "default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self'; object-src 'none'",
            'X-XSS-Protection' => '1; mode=block'
        ];
    }
}