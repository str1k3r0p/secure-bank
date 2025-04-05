<?php
/**
 * Banking DVWA Project
 * Directory Traversal Base Class
 * 
 * This abstract class defines the base for directory traversal vulnerability implementations.
 */

namespace Vulnerabilities\directory_traversal;

use Vulnerabilities\VulnerabilityBase;

abstract class DirectoryTraversal extends VulnerabilityBase
{
    /**
     * @var string Vulnerability name
     */
    protected $name = 'Directory Traversal';
    
    /**
     * @var string Vulnerability description
     */
    protected $description = 'Directory Traversal (also known as Path Traversal) is a vulnerability that allows an attacker to access files and directories that are stored outside the web root folder. By manipulating variables that reference files with "dot-dot-slash (../)" sequences and its variations, an attacker can access arbitrary files and directories stored on the file system.';
    
    /**
     * @var string Base directory for demonstration files
     */
    protected $baseDir;
    
    /**
     * Constructor
     * 
     * @param string $securityLevel Security level
     */
    public function __construct($securityLevel = null)
    {
        parent::__construct($securityLevel);
        
        // Set the base directory for demonstration files
        $this->baseDir = PUBLIC_PATH . '/assets/demo/sample_statements';
        
        // Create demo files if they don't exist
        $this->createDemoFiles();
    }
    
    /**
     * Get the vulnerability type
     * 
     * @return string Vulnerability type
     */
    public function getType()
    {
        return 'directory_traversal';
    }
    
    /**
     * Execute the vulnerability
     * 
     * @param array $input Input data
     * @return mixed Result
     */
    abstract public function execute($input);
    
    /**
     * Create demo files for directory traversal
     */
    protected function createDemoFiles()
    {
        // Create base directory if it doesn't exist
        if (!file_exists($this->baseDir)) {
            mkdir($this->baseDir, 0755, true);
        }
        
        // Create demo files
        $demoFiles = [
            'statement_account1.pdf' => "Account Statement 1\nAccount: 1234567890\nBalance: $5,000.00\nDate: 2023-01-01",
            'statement_account2.pdf' => "Account Statement 2\nAccount: 0987654321\nBalance: $10,000.00\nDate: 2023-01-01",
            'statement_account3.pdf' => "Account Statement 3\nAccount: 5678901234\nBalance: $2,500.00\nDate: 2023-01-01"
        ];
        
        foreach ($demoFiles as $filename => $content) {
            $filePath = $this->baseDir . '/' . $filename;
            if (!file_exists($filePath)) {
                file_put_contents($filePath, $content);
            }
        }
    }
    
    /**
     * Get a list of available demo files
     * 
     * @return array List of demo files
     */
    public function getAvailableFiles()
    {
        $files = [];
        
        if (file_exists($this->baseDir) && is_dir($this->baseDir)) {
            $dirContents = scandir($this->baseDir);
            
            foreach ($dirContents as $item) {
                if ($item !== '.' && $item !== '..' && is_file($this->baseDir . '/' . $item)) {
                    $files[] = $item;
                }
            }
        }
        
        return $files;
    }
}