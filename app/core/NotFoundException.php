<?php
/**
 * Banking DVWA Project
 * Not Found Exception
 * 
 * This exception is thrown when a requested resource is not found.
 */

namespace App\Core;

class NotFoundException extends \Exception
{
    /**
     * Constructor
     * 
     * @param string $message The exception message
     * @param int $code The exception code
     * @param \Throwable $previous The previous exception
     */
    public function __construct($message = "Resource not found", $code = 404, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}