<?php
/**
 * Banking DVWA Project
 * Directory Traversal - High Security Implementation
 * 
 * This class implements the directory traversal vulnerability with high security.
 * It uses a whitelist approach and prevents access to files outside the intended directory.
 */

namespace Vulnerabilities\directory_traversal;

use App\Core\Logger;

class HighSecurity extends DirectoryTraversal
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
        
        // SECURE: Validate that the filename is in the whitelist
        $available_files = $this->getAvailableFiles();
        if (!in_array($filename, $available_files)) {
            Logger::security("Directory traversal attempt blocked (HIGH)", [
                'filename' => $filename,
                'ip' => get_client_ip(),
                'user_id' => $_SESSION['user_id'] ?? 'guest'
            ]);
            
            return [
                'content' => '',
                'filepath' => '',
                'error' => 'Invalid filename. Please choose from the available files.'
            ];
        }
        
        // Construct file path with validated filename
        $filepath = $this->baseDir . '/' . $filename;
        
        // Extra Security: Validate that the resolved path is within the base directory
        $realpath = realpath($filepath);
        $real_base_dir = realpath($this->baseDir);
        
        if (!$realpath || strpos($realpath, $real_base_dir) !== 0) {
            Logger::security("Directory traversal attempt blocked - path validation (HIGH)", [
                'filename' => $filename,
                'filepath' => $filepath,
                'realpath' => $realpath ?? 'N/A',
                'ip' => get_client_ip(),
                'user_id' => $_SESSION['user_id'] ?? 'guest'
            ]);
            
            return [
                'content' => '',
                'filepath' => $filepath,
                'error' => 'Security violation: Attempted to access file outside the allowed directory.'
            ];
        }
        
        // Check if file is readable
        if (!is_readable($filepath)) {
            return [
                'content' => '',
                'filepath' => $filepath,
                'error' => 'File is not readable'
            ];
        }
        
        // Read file content
        $content = file_get_contents($filepath);
        
        // Log the file access
        Logger::security("Directory traversal file access (HIGH - secure)", [
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