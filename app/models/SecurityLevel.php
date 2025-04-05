<?php
/**
 * Banking DVWA Project
 * Security Level Model
 * 
 * Manages security levels for the vulnerability demonstrations.
 */

namespace App\Models;

use App\Core\Model;
use App\Core\Logger;

class SecurityLevel extends Model
{
    /**
     * @var string Database table name
     */
    protected $table = 'security_levels';
    
    /**
     * @var string Primary key field
     */
    protected $primaryKey = 'id';
    
    /**
     * @var array Fields that can be mass-assigned
     */
    protected $fillable = [
        'vulnerability',
        'level',
        'description',
        'user_id',
        'updated_at'
    ];
    
    /**
     * @var array Validation rules for fields
     */
    protected $rules = [
        'vulnerability' => 'required',
        'level' => 'required'
    ];
    
    /**
     * Get security level for a vulnerability
     * 
     * @param string $vulnerability Vulnerability type
     * @return string Security level (low, medium, high)
     */
    public function getLevel($vulnerability)
    {
        $securityLevel = $this->findOneBy('vulnerability', $vulnerability);
        
        if (!$securityLevel) {
            return DEFAULT_SECURITY_LEVEL;
        }
        
        return $securityLevel['level'];
    }
    
    /**
     * Set security level for a vulnerability
     * 
     * @param string $vulnerability Vulnerability type
     * @param string $level Security level
     * @param int $userId User ID
     * @return bool Success status
     */
    public function setLevel($vulnerability, $level, $userId)
    {
        // Validate level
        if (!in_array($level, SECURITY_LEVELS)) {
            Logger::warning("Invalid security level", [
                'vulnerability' => $vulnerability,
                'level' => $level
            ]);
            return false;
        }
        
        // Get existing record
        $existingLevel = $this->findOneBy('vulnerability', $vulnerability);
        
        if ($existingLevel) {
            // Update existing record
            $success = $this->update($existingLevel['id'], [
                'level' => $level,
                'user_id' => $userId,
                'updated_at' => date(DATETIME_FORMAT)
            ]);
        } else {
            // Create new record
            $success = (bool) $this->create([
                'vulnerability' => $vulnerability,
                'level' => $level,
                'user_id' => $userId,
                'updated_at' => date(DATETIME_FORMAT)
            ]);
        }
        
        if ($success) {
            Logger::info("Security level set", [
                'vulnerability' => $vulnerability,
                'level' => $level,
                'user_id' => $userId
            ]);
        }
        
        return $success;
    }
    
    /**
     * Get all security levels
     * 
     * @return array Security levels for all vulnerabilities
     */
    public function getAllLevels()
    {
        $levels = $this->all();
        $result = [];
        
        // Create associative array
        foreach ($levels as $level) {
            $result[$level['vulnerability']] = $level['level'];
        }
        
        // Add default levels for vulnerabilities not in the database
        $vulnerabilities = [
            'brute_force',
            'cmd_injection',
            'sql_injection',
            'directory_traversal',
            'xss'
        ];
        
        foreach ($vulnerabilities as $vulnerability) {
            if (!isset($result[$vulnerability])) {
                $result[$vulnerability] = DEFAULT_SECURITY_LEVEL;
            }
        }
        
        return $result;
    }
    
    /**
     * Reset all security levels to default
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public function resetAll($userId)
    {
        // Begin transaction
        $this->db->beginTransaction();
        
        try {
            $vulnerabilities = [
                'brute_force',
                'cmd_injection',
                'sql_injection',
                'directory_traversal',
                'xss'
            ];
            
            foreach ($vulnerabilities as $vulnerability) {
                $this->setLevel($vulnerability, DEFAULT_SECURITY_LEVEL, $userId);
            }
            
            // Commit transaction
            $this->db->commit();
            
            Logger::info("All security levels reset", ['user_id' => $userId]);
            
            return true;
        } catch (\Exception $e) {
            // Rollback transaction
            $this->db->rollback();
            
            Logger::error("Failed to reset security levels", [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            
            return false;
        }
    }
    
    /**
     * Get description for a security level
     * 
     * @param string $level Security level
     * @return string Description
     */
    public function getLevelDescription($level)
    {
        $descriptions = [
            'low' => 'Low security level has no protection against the vulnerability, ' .
                    'demonstrating how the vulnerability works in its most basic form.',
                    
            'medium' => 'Medium security level implements basic protection measures ' .
                       'that can be bypassed with more advanced techniques.',
                       
            'high' => 'High security level implements strong protection measures ' .
                     'that mitigate the vulnerability according to best practices.'
        ];
        
        return $descriptions[$level] ?? 'Unknown security level';
    }
    
    /**
     * Get vulnerability description
     * 
     * @param string $vulnerability Vulnerability type
     * @return string Description
     */
    public function getVulnerabilityDescription($vulnerability)
    {
        $descriptions = [
            'brute_force' => 'Brute Force attacks involve systematically trying all possible ' .
                            'combinations of credentials until finding the correct one.',
                            
            'cmd_injection' => 'Command Injection vulnerabilities allow attackers to execute ' .
                              'system commands on the host through the web application.',
                              
            'sql_injection' => 'SQL Injection vulnerabilities allow attackers to manipulate ' .
                              'database queries to access or modify unauthorized data.',
                              
            'directory_traversal' => 'Directory Traversal vulnerabilities allow attackers to ' .
                                    'access files and directories outside the intended directory.',
                                    
            'xss' => 'Cross-Site Scripting (XSS) vulnerabilities allow attackers to inject ' .
                    'malicious scripts that execute in users\' browsers.'
        ];
        
        return $descriptions[$vulnerability] ?? 'Unknown vulnerability';
    }
}