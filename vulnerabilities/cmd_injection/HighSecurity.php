<?php
/**
 * Banking DVWA Project
 * Command Injection - High Security Implementation
 * 
 * This class implements the command injection vulnerability with high security.
 * It uses a whitelist approach and validates all input parameters.
 */

namespace Vulnerabilities\cmd_injection;

use App\Core\Logger;

class HighSecurity extends CommandInjection
{
    /**
     * Execute the vulnerability
     * 
     * @param array $input Input data
     * @return array Command execution results
     */
    public function execute($input)
    {
        // Check if command is provided
        if (!isset($input['command']) || empty($input['command']) || !isset($input['param']) || empty($input['param'])) {
            return [
                'output' => '',
                'command' => '',
                'error' => 'Please provide both a command and a parameter'
            ];
        }
        
        // Get command and parameter from input
        $command = $input['command'];
        $param = $input['param'];
        
        // Make sure the command is one of the allowed commands
        $allowed_commands = array_keys($this->getAllowedCommands());
        if (!in_array($command, $allowed_commands)) {
            return [
                'output' => '',
                'command' => $command,
                'error' => 'Command not allowed. Please use one of the allowed commands.'
            ];
        }
        
        // SECURE: Strictly validate the parameter based on command type
        $validation_result = $this->validateParameter($command, $param);
        
        if ($validation_result !== true) {
            return [
                'output' => '',
                'command' => $command . ' ' . $param,
                'error' => $validation_result
            ];
        }
        
        // Sanitize the parameter based on expected format
        $sanitized_param = $this->sanitizeParameter($command, $param);
        
        // Build the command
        $full_command = $command . ' ' . $sanitized_param;
        
        // For the purpose of this demo, we'll simulate command execution
        $output = $this->simulateCommandExecution($full_command);
        
        // Log the command execution
        Logger::security("Command injection executed (HIGH)", [
            'command' => $full_command,
            'ip' => get_client_ip(),
            'user_id' => $_SESSION['user_id'] ?? 'guest'
        ]);
        
        return [
            'output' => $output,
            'command' => $full_command,
            'error' => null
        ];
    }
    
    /**
     * Validate parameter based on command type
     * 
     * @param string $command The command
     * @param string $param The parameter
     * @return true|string True if valid, error message otherwise
     */
    private function validateParameter($command, $param)
    {
        switch ($command) {
            case 'ping':
            case 'traceroute':
                // For ping and traceroute, allow only hostnames and IP addresses
                if (!$this->isValidHostnameOrIP($param)) {
                    return 'Parameter must be a valid hostname or IP address';
                }
                break;
                
            case 'nslookup':
            case 'whois':
                // For nslookup and whois, allow only domain names
                if (!$this->isValidDomain($param)) {
                    return 'Parameter must be a valid domain name';
                }
                break;
                
            default:
                return 'Unsupported command';
        }
        
        return true;
    }
    
    /**
     * Sanitize parameter based on command type
     * 
     * @param string $command The command
     * @param string $param The parameter
     * @return string The sanitized parameter
     */
    private function sanitizeParameter($command, $param)
    {
        switch ($command) {
            case 'ping':
            case 'traceroute':
                // For ping and traceroute, sanitize hostname or IP
                return preg_replace('/[^a-zA-Z0-9.-]/', '', $param);
                
            case 'nslookup':
            case 'whois':
                // For nslookup and whois, sanitize domain name
                return preg_replace('/[^a-zA-Z0-9.-]/', '', $param);
                
            default:
                return '';
        }
    }
    
    /**
     * Check if a string is a valid hostname or IP address
     * 
     * @param string $input The input to check
     * @return bool True if valid
     */
    private function isValidHostnameOrIP($input)
    {
        // Check if it's a valid IP address
        if (filter_var($input, FILTER_VALIDATE_IP)) {
            return true;
        }
        
        // Check if it's a valid hostname
        return (bool) preg_match('/^[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?)*$/', $input);
    }
    
    /**
     * Check if a string is a valid domain name
     * 
     * @param string $input The input to check
     * @return bool True if valid
     */
    private function isValidDomain($input)
    {
        return (bool) preg_match('/^[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?)+$/', $input);
    }
    
    /**
     * Generate sample command for demonstration
     * 
     * @param string $userInput User input for the command
     * @return string The sample command
     */
    public function generateSampleCommand($userInput)
    {
        $sanitized = preg_replace('/[^a-zA-Z0-9.-]/', '', $userInput);
        return "ping " . $sanitized;
    }
    
    /**
     * Simulate command execution
     * 
     * This function simulates the execution of system commands for demonstration purposes.
     * In a real secure application, this would use carefully controlled command execution.
     * 
     * @param string $command The command to simulate
     * @return string The simulated output
     */
    private function simulateCommandExecution($command)
    {
        // Extract the base command and parameters
        $parts = explode(' ', $command, 2);
        $base_command = $parts[0];
        $parameters = isset($parts[1]) ? $parts[1] : '';
        
        // Simulate output for allowed commands with sanitized parameters
        switch ($base_command) {
            case 'ping':
                return "PING {$parameters} 56(84) bytes of data.\n64 bytes from {$parameters}: icmp_seq=1 ttl=64 time=0.1 ms\n64 bytes from {$parameters}: icmp_seq=2 ttl=64 time=0.2 ms\n64 bytes from {$parameters}: icmp_seq=3 ttl=64 time=0.1 ms\n64 bytes from {$parameters}: icmp_seq=4 ttl=64 time=0.2 ms\n\n--- {$parameters} ping statistics ---\n4 packets transmitted, 4 received, 0% packet loss, time 3ms\nrtt min/avg/max/mdev = 0.101/0.150/0.204/0.052 ms";
                
            case 'nslookup':
                return "Server:\t\t8.8.8.8\nAddress:\t8.8.8.8#53\n\nNon-authoritative answer:\nName:\t{$parameters}\nAddress: 93.184.216.34";
                
            case 'traceroute':
                return "traceroute to {$parameters}, 30 hops max, 60 byte packets\n 1  gateway (192.168.1.1)  1.043 ms  0.913 ms  0.881 ms\n 2  192.168.100.1 (192.168.100.1)  9.776 ms  9.743 ms  9.709 ms\n 3  172.16.10.1 (172.16.10.1)  19.903 ms  19.890 ms  19.316 ms\n 4  * * *\n 5  * * *\n 6  {$parameters} (93.184.216.34)  87.312 ms  86.868 ms  86.829 ms";
                
            case 'whois':
                return "Domain Name: {$parameters}\nRegistry Domain ID: 2336799_DOMAIN_COM-VRSN\nRegistrar WHOIS Server: whois.example-registrar.com\nRegistrar URL: http://www.example-registrar.com\nUpdated Date: 2020-09-12T10:38:52Z\nCreation Date: 1997-03-29T05:00:00Z\nRegistry Expiry Date: 2028-03-30T04:00:00Z\nRegistrar: Example Registrar, LLC\nRegistrar IANA ID: 1234567\nRegistrar Abuse Contact Email: abuse@example-registrar.com\nRegistrar Abuse Contact Phone: +1.5555551234";
                
            default:
                return "Command not recognized: {$base_command}";
        }
    }
}