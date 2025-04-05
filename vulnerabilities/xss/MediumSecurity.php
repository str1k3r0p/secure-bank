<?php
/**
 * Banking DVWA Project
 * XSS (Cross-Site Scripting) - Medium Security Implementation
 * 
 * This class implements the XSS vulnerability with medium security.
 * It attempts to filter some dangerous HTML but can still be bypassed.
 */

namespace Vulnerabilities\xss;

use App\Core\Logger;

class MediumSecurity extends XSS
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
        
        // SOMEWHAT SECURE: Basic filtering of some dangerous HTML
        $filtered_message = $this->filterXSS($message);
        
        // Log original vs. filtered message
        if ($message !== $filtered_message) {
            Logger::security("XSS attempt filtered (MEDIUM)", [
                'original' => $message,
                'filtered' => $filtered_message,
                'ip' => get_client_ip(),
                'user_id' => $_SESSION['user_id'] ?? 'guest'
            ]);
        }
        
        // Add the filtered message
        $this->addMessage($user, $filtered_message);
        
        return [
            'success' => true,
            'original_message' => $message,
            'filtered_message' => $filtered_message,
            'messages' => $this->getMessages()
        ];
    }
    
    /**
     * Filter some dangerous HTML
     * 
     * @param string $input User input
     * @return string Filtered input
     */
    private function filterXSS($input)
    {
        // This filtering is intentionally incomplete to demonstrate that partial
        // filtering can still be bypassed
        
        // Remove some dangerous HTML tags
        $filtered = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $input);
        
        // Remove on* event handlers
        $filtered = preg_replace('/on\w+\s*=\s*"[^"]*"/i', '', $filtered);
        $filtered = preg_replace('/on\w+\s*=\s*\'[^\']*\'/i', '', $filtered);
        
        // Remove javascript: URLs
        $filtered = preg_replace('/javascript\s*:/i', 'blocked:', $filtered);
        
        return $filtered;
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
        $html .= '<span class="message-user">' . htmlspecialchars($message['user']) . '</span>';
        $html .= '<span class="message-time">' . date('M j, Y g:i A', $message['timestamp']) . '</span>';
        $html .= '</div>';
        $html .= '<div class="message-content">' . $message['message'] . '</div>';
        $html .= '</div>';
        
        return $html;
    }
}