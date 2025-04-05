<?php
/**
 * Banking DVWA Project
 * Directory Traversal - Medium Security Implementation
 * 
 * This class implements the directory traversal vulnerability with medium security.
 * It attempts to filter out directory traversal sequences but can still be bypassed.
 */

namespace Vulnerabilities\directory_traversal;

use App\Core\Logger;

class MediumSecurity extends DirectoryTraversal
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
        
        // SOMEWHAT SECURE: Try to remove directory traversal sequences
        // This filtering is intentionally flawed
        $filtered_filename = str_replace(['../', '..\\'], '', $filename);
        
        // Log original vs. filtered filename
        if ($filename !== $filtered_filename) {
            Logger::security("Directory traversal attempt filtered (MEDIUM)", [
                'original' => $filename,
                'filtered' => $filtered_filename,
                'ip' => get_client_ip(),
                'user_id' => $_SESSION['user_id'] ?? 'guest'
            ]);
        }
        
        // Construct file path with filtered filename
        $filepath = $this->baseDir . '/' . $filtered_filename;
        
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
        Logger::security("Directory traversal file access (MEDIUM)", [
            'filepath' => $filepath,
            'ip' => get_client_ip(),
            'user_id' => $_SESSION['user_id'] ?? 'guest'
        ]);
        
        return [
            'content' => $content,
            'filepath' => $filepath,
            'original_filename' => $filename,
            'filtered_filename' => $filtered_filename,
            'error' => null
        ];
    }
}