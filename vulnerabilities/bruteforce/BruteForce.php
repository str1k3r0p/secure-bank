<?php
/**
 * Banking DVWA Project
 * Brute Force Base Class
 * 
 * This abstract class defines the base for brute force vulnerability implementations.
 */

namespace Vulnerabilities\bruteforce;

use Vulnerabilities\VulnerabilityBase;

abstract class BruteForce extends VulnerabilityBase
{
    /**
     * @var string Vulnerability name
     */
    protected $name = 'Brute Force';
    
    /**
     * @var string Vulnerability description
     */
    protected $description = 'Brute Force is an attack method that involves systematically checking all possible passwords until the correct one is found. This vulnerability demonstration shows how different security measures can prevent or slow down brute force attacks.';
    
    /**
     * Get the vulnerability type
     * 
     * @return string Vulnerability type
     */
    public function getType()
    {
        return 'brute_force';
    }
    
    /**
     * Execute the vulnerability
     * 
     * @param array $input Input data
     * @return mixed Result
     */
    abstract public function execute($input);
}