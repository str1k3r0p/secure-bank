<?php
/**
 * Banking DVWA Project
 * Command Injection - Low Security Implementation
 * 
 * This class implements the command injection vulnerability with low security.
 * It directly executes user input as system commands without any validation or sanitization.
 */

namespace Vulnerabilities\cmd_injection;

use App\Core\Logger;

class LowSecurity extends CommandInjection
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
        
        // VULNERABLE: Direct command execution without parameter sanitization
        $full_command = $command . ' ' . $param;
        
        // For the purpose of this demo, we'll simulate command execution
        // In a real environment, this would use exec(), system(), passthru(), etc.
        $output = $this->simulateCommandExecution($full_command);
        
        // Log the command execution
        Logger::security("Command injection executed (LOW)", [
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
     * Generate sample command for demonstration
     * 
     * @param string $userInput User input for the command
     * @return string The sample command
     */
    public function generateSampleCommand($userInput)
    {
        return "ping " . $userInput;
    }
    
    /**
     * Simulate command execution
     * 
     * This function simulates the execution of system commands for demonstration purposes.
     * In a real vulnerable application, this would use actual system command execution functions.
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
        
        // Check for command injection
        if (preg_match('/[;&|`\(\)\$]/', $command)) {
            // Simulate the effect of command injection
            if (strpos($command, ';') !== false) {
                $cmds = explode(';', $command);
                $output = "Executing multiple commands:\n\n";
                
                foreach ($cmds as $cmd) {
                    $cmd = trim($cmd);
                    if (!empty($cmd)) {
                        $output .= "$ " . $cmd . "\n";
                        
                        // Simulate common command outputs
                        if (strpos($cmd, 'ls') === 0) {
                            $output .= "config.php\nindex.php\n.htaccess\nREADME.md\nuploads/\nassets/\n";
                        } elseif (strpos($cmd, 'whoami') === 0) {
                            $output .= "www-data\n";
                        } elseif (strpos($cmd, 'id') === 0) {
                            $output .= "uid=33(www-data) gid=33(www-data) groups=33(www-data)\n";
                        } elseif (strpos($cmd, 'cat') === 0) {
                            if (strpos($cmd, 'passwd') !== false) {
                                $output .= "root:x:0:0:root:/root:/bin/bash\ndaemon:x:1:1:daemon:/usr/sbin:/usr/sbin/nologin\n... [truncated for security]\n";
                            } elseif (strpos($cmd, 'config') !== false) {
                                $output .= "<?php\n// Database configuration\ndefine('DB_HOST', 'localhost');\ndefine('DB_NAME', 'bank_dvwa');\ndefine('DB_USER', 'dbuser');\ndefine('DB_PASS', 'dbpassword');\n?>\n";
                            } else {
                                $output .= "[File contents would appear here]\n";
                            }
                        } elseif (strpos($cmd, 'pwd') === 0) {
                            $output .= "/var/www/html/bank_dvwa_project\n";
                        } else {
                            $output .= "[Command output would appear here]\n";
                        }
                    }
                }
                
                return $output;
            } elseif (strpos($command, '&&') !== false) {
                $cmds = explode('&&', $command);
                $output = "Executing commands sequentially (&&):\n\n";
                
                foreach ($cmds as $cmd) {
                    $cmd = trim($cmd);
                    if (!empty($cmd)) {
                        $output .= "$ " . $cmd . "\n[Command output would appear here]\n\n";
                    }
                }
                
                return $output;
            } elseif (strpos($command, '|') !== false) {
                return "Executing piped command: " . $command . "\n[Piped command output would appear here]\n";
            } elseif (strpos($command, '`') !== false || strpos($command, '$(') !== false) {
                return "Executing command substitution: " . $command . "\n[Command substitution output would appear here]\n";
            }
        }
        
        // Simulate output for normal commands
        switch ($base_command) {
            case 'ping':
                $host = preg_replace('/[^a-zA-Z0-9.-]/', '', $parameters);
                return "PING {$host} 56(84) bytes of data.\n64 bytes from {$host}: icmp_seq=1 ttl=64 time=0.1 ms\n64 bytes from {$host}: icmp_seq=2 ttl=64 time=0.2 ms\n64 bytes from {$host}: icmp_seq=3 ttl=64 time=0.1 ms\n64 bytes from {$host}: icmp_seq=4 ttl=64 time=0.2 ms\n\n--- {$host} ping statistics ---\n4 packets transmitted, 4 received, 0% packet loss, time 3ms\nrtt min/avg/max/mdev = 0.101/0.150/0.204/0.052 ms";
                
            case 'nslookup':
                $domain = preg_replace('/[^a-zA-Z0-9.-]/', '', $parameters);
                return "Server:\t\t8.8.8.8\nAddress:\t8.8.8.8#53\n\nNon-authoritative answer:\nName:\t{$domain}\nAddress: 93.184.216.34";
                
            case 'traceroute':
                $host = preg_replace('/[^a-zA-Z0-9.-]/', '', $parameters);
                return "traceroute to {$host}, 30 hops max, 60 byte packets\n 1  gateway (192.168.1.1)  1.043 ms  0.913 ms  0.881 ms\n 2  192.168.100.1 (192.168.100.1)  9.776 ms  9.743 ms  9.709 ms\n 3  172.16.10.1 (172.16.10.1)  19.903 ms  19.890 ms  19.316 ms\n 4  * * *\n 5  * * *\n 6  {$host} (93.184.216.34)  87.312 ms  86.868 ms  86.829 ms";
                
            case 'whois':
                $domain = preg_replace('/[^a-zA-Z0-9.-]/', '', $parameters);
                return "Domain Name: {$domain}\nRegistry Domain ID: 2336799_DOMAIN_COM-VRSN\nRegistrar WHOIS Server: whois.example-registrar.com\nRegistrar URL: http://www.example-registrar.com\nUpdated Date: 2020-09-12T10:38:52Z\nCreation Date: 1997-03-29T05:00:00Z\nRegistry Expiry Date: 2028-03-30T04:00:00Z\nRegistrar: Example Registrar, LLC\nRegistrar IANA ID: 1234567\nRegistrar Abuse Contact Email: abuse@example-registrar.com\nRegistrar Abuse Contact Phone: +1.5555551234";
                
            default:
                return "Command not recognized: {$base_command}";
        }
    }
}