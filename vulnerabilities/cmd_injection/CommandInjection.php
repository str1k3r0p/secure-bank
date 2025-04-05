<?php
/**
 * Banking DVWA Project
 * Command Injection Base Class
 * 
 * This abstract class defines the base for command injection vulnerability implementations.
 */

namespace Vulnerabilities\cmd_injection;

use Vulnerabilities\VulnerabilityBase;

abstract class CommandInjection extends VulnerabilityBase
{
    /**
     * @var string Vulnerability name
     */
    protected $name = 'Command Injection';
    
    /**
     * @var string Vulnerability description
     */
    protected $description = 'Command Injection is an attack in which the goal is to execute arbitrary commands on the host operating system via a vulnerable application. Command injection attacks are possible when an application passes unsafe user supplied data (forms, cookies, HTTP headers, etc.) to a system shell.';
    
    /**
     * Get the vulnerability type
     * 
     * @return string Vulnerability type
     */
    public function getType()
    {
        return 'cmd_injection';
    }
    
    /**
     * Execute the vulnerability
     * 
     * @param array $input Input data
     * @return mixed Result
     */
    abstract public function execute($input);
    
    /**
     * Generate sample command for demonstration
     * 
     * @param string $userInput User input for the command
     * @return string The sample command
     */
    abstract public function generateSampleCommand($userInput);
    
    /**
     * Get allowed commands for demonstration
     * 
     * @return array List of allowed commands
     */
    public function getAllowedCommands()
    {
        return [
            'ping' => 'Ping a host to check connectivity',
            'nslookup' => 'Query DNS records for a domain',
            'traceroute' => 'Trace the route to a host',
            'whois' => 'Get WHOIS information for a domain'
        ];
    }
}