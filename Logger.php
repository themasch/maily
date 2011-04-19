<?php
/**
 * Interface for all loggers
 * 
 * @author Mark Schmale
 */
interface Logger {
    public function write($msg);
    public function close();
}
