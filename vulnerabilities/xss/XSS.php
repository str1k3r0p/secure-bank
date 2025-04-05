<?php
/**
 * Banking DVWA Project
 * XSS (Cross-Site Scripting) Base Class
 * 
 * This abstract class defines the base for XSS vulnerability implementations.
 */

namespace Vulnerabilities\xss;

use Vulnerabilities\VulnerabilityBase;

abstract class XSS extends VulnerabilityBase
{
    /**
     * @var string Vulnerability name
     */
    protected $name = 'Cross-Site Scripting (XSS)';
    
    /**
     * @var string Vulnerability description
     */
    protected $description = 'Cross-Site Scripting (XSS) is a client-side code injection attack. The attacker aims to execute malicious scripts in a web browser by including malicious code in a legitimate web page or web application. The actual attack occurs when the victim visits the web page or web application that executes the malicious code.';
    
    /**
     * @var array Stored messages for demonstration
     */
    protected $messages = [];
    
    /**
     * Constructor
     * 
     * @param string $securityLevel Security level
     */
    public function __construct($securityLevel = null)
    {
        parent::__construct($securityLevel);
        
        // Initialize stored messages
        $this->initializeMessages();
    }
    
    /**
     * Get the vulnerability type
     * 
     * @return string Vulnerability type
     */
    public function getType()
    {
        return 'xss';
    }
    
    /**
     * Execute the vulnerability
     * 
     * @param array $input Input data
     * @return mixed Result
     */
    abstract public function execute($input);
    
    /**
     * Initialize stored messages
     */
    protected function initializeMessages()
    {
        // If session messages don't exist, create default ones
        if (!isset($_SESSION['xss_messages'])) {
            $_SESSION['xss_messages'] = [
                [
                    'user' => 'John',
                    'message' => 'Hello! Welcome to our banking message board.',
                    'timestamp' => time() - 3600
                ],
                [
                    'user' => 'Alice',
                    'message' => 'Has anyone experienced issues with the new mobile app?',
                    'timestamp' => time() - 2400
                ],
                [
                    'user' => 'Bob',
                    'message' => 'The transfer feature works great! Very convenient.',
                    'timestamp' => time() - 1200
                ]
            ];
        }
        
        $this->messages = &$_SESSION['xss_messages'];
    }
    
    /**
     * Add a new message
     * 
     * @param string $user User name
     * @param string $message Message content
     */
    protected function addMessage($user, $message)
    {
        $this->messages[] = [
            'user' => $user,
            'message' => $message,
            'timestamp' => time()
        ];
        
        // Keep only the last 10 messages
        if (count($this->messages) > 10) {
            array_shift($this->messages);
        }
    }
    
    /**
     * Get all messages
     * 
     * @return array All messages
     */
    public function getMessages()
    {
        return $this->messages;
    }
}