<?php

namespace Maily;
use Maily\Log;

require_once __DIR__.'/Logger.php';
require_once __DIR__.'/Logger/File.php';
require_once __DIR__.'/Logger/StdOut.php';
require_once __DIR__.'/Logger/StdErr.php';

/**
 * small logger 
 *
 * @package Maily
 * @author Mark Schmale
 */
class Log {

    // Log Level
    const INFO = 'info';
    const WARN = 'warn';
    const ERROR = 'err';

    /**
     * list of all loggers sorted by level
     * @var array(array(Logger))
     */
    protected static $targets = array();

    /**
     * variable to check is the Loggers are closed
     * @var bool
     */
    protected static $closed = false;
   
    /**
     * initializes the logger
     *
     * @param array $config an array for configuration 
     */
    public static function setUp(array $config)
    {
        if(!isset($config['targets'])) {
            return;
        }

        foreach($config['targets'] as $target) {
            $t = null;

            // TODO: DYNAMIC!
            switch ($target['type']) {
                case 'file':
                    $t = new Logger\File($target['path']);
                    break;
                case 'echo':
                    $t = new Logger\StdOut();
                    break;
                case 'stderr':
                    $t = new Logger\StdErr();
                    break;
            }
            
            if($t != null) {
                self::appendLogger($target['level'], $t);
            }
        }
    }

    /**
     * appends a logger to one or multiple log levels
     *
     * @param string|array $level log level
     * @param Logger       $logger the logger
     */
    public static function appendLogger($level, Logger $logger) 
    {
        if(is_array($level)) {
            // array, call for every entry
            foreach($level as $l) {
                self::appendLogger($l, $logger);
            }
        } else {
            // no array, append logger
            if(!isset(self::$targets[$level]) || !is_array(self::$targets[$level])) {
                self::$targets[$level] = array();
            }
            self::$targets[$level][] = $logger;
        }
    }
    
    /**
     * writes a log message to the selected targets
     * 
     * @param mixed    $msg     Message that should be logged
     * @param stroing  $level   Log Level. Defaults to INFO
     */
    public static function write($msg, $level=Log::INFO)
    {
        if(!isset(self::$targets[$level]) || 
           !is_array(self::$targets[$level]) ) {
            return;
        }

        foreach(self::$targets[$level] as $t) {
            if($t instanceof Logger) {
                $t->write($msg);
            }
        }
    }
    
    /**
     * closes all loggers
     */
    public static function close()
    {
        foreach(self::$targets as $t)
        {
            if($t instanceof Logger)
                $t->close ();
        }
    }
}
