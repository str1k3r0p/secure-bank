<?php
/**
 * Banking DVWA Project
 * Directory Traversal - Low Security Implementation
 * 
 * This class implements the directory traversal vulnerability with low security.
 * It directly uses user input in file paths without any validation or sanitization.
 */

namespace Vulnerabilities\directory_traversal;

use App\Core\Logger;

class LowSecurity extends DirectoryTraversal
{
    /**
     * Execute the vulnerability
     * 
     * @param array $input Input data
     * @return array File retrieval results
     */
    public function execute($input)
    {
        // Check if filename is provided
        if (!isset($input['file']) || empty($input['file'])) {
            return [
                'content' => '',
                'filepath' => '',
                'error' => 'Please provide a filename'
            ];
        }
        
        // Get filename from input
        $filename = $input['file'];
        
        // VULNERABLE: Direct use of user input in file path
        $filepath = $this->baseDir . '/' . $filename;
        
        // Check if file exists
        if (!file_exists($filepath)) {
            return [
                'content' => '',
                'filepath' => $filepath,
                'error' => 'File not found: ' . $filepath
            ];
        }
        
        // Check if file is readable
        if (!is_readable($filepath)) {
            return [
                'content' => '',
                'filepath' => $filepath,
                'error' => 'File is not readable: ' . $filepath
            ];
        }
        
        // Read file content
        $content = file_get_contents($filepath);
        
        // Log the file access
        Logger::security("Directory traversal file access (LOW)", [
            'filepath' => $filepath,
            'ip' => get_client_ip(),
            'user_id' => $_SESSION['user_id'] ?? 'guest'
        ]);
        
        return [
            'content' => $content,
            'filepath' => $filepath,
            'error' => null
        ];
    }
}