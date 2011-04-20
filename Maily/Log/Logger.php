<?php

namespace Maily\Log;

/**
 * Interface for all loggers
 * 
 * @author Mark Schmale
 */
interface Logger {
    
    /**
     * sends a log message to the logger
     * @param mixed $msg the message
     */
    public function write($msg);
    
    /**
     * closes all resources in the logger
     */
    public function close();
}
