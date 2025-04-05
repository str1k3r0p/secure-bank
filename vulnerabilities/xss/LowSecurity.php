<?php
/**
 * Banking DVWA Project
 * XSS (Cross-Site Scripting) - Low Security Implementation
 * 
 * This class implements the XSS vulnerability with low security.
 * It directly outputs user input without any validation or sanitization.
 */

namespace Vulnerabilities\xss;

use App\Core\Logger;

class LowSecurity extends XSS
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
        
        // VULNERABLE: No sanitization of user input
        $this->addMessage($user, $message);
        
        // Log the XSS attempt
        Logger::security("XSS message posted (LOW)", [
            'message' => $message,
            'ip' => get_client_ip(),
            'user_id' => $_SESSION['user_id'] ?? 'guest'
        ]);
        
        return [
            'success' => true,
            'messages' => $this->getMessages()
        ];
    }
    
    /**
     * Render a message for display
     * 
     * @param array $message Message data
     * @return string Rendered HTML
     */
    public function renderMessage($message)
    {
        // VULNERABLE: Direct output of user input without sanitization
        $html = '<div class="message-container">';
        $html .= '<div class="message-header">';
        $html .= '<span class="message-user">' . $message['user'] . '</span>';
        $html .= '<span class="message-time">' . date('M j, Y g:i A', $message['timestamp']) . '</span>';
        $html .= '</div>';
        $html .= '<div class="message-content">' . $message['message'] . '</div>';
        $html .= '</div>';
        
        return $html;
    }
}