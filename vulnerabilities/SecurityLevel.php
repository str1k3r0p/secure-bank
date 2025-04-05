<?php
/**
 * Banking DVWA Project
 * Security Level Interface
 * 
 * This interface defines methods for security level implementations.
 */

namespace Vulnerabilities;

interface SecurityLevel
{
    /**
     * Get the security level
     * 
     * @return string The security level (low, medium, high)
     */
    public function getLevel();
    
    /**
     * Get the security level description
     * 
     * @return string The security level description
     */
    public function getDescription();
    
    /**
     * Execute the security level implementation
     * 
     * @param array $input The input data
     * @return mixed The result
     */
    public function execute($input);
    
    /**
     * Get the source code
     * 
     * @return string The source code
     */
    public function getSourceCode();
}