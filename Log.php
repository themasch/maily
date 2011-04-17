<?php
require_once './FileLogger.php';
require_once './EchoLogger.php';
/**
 * logger
 * @author Mark Schmale
 */
class Log {
    
    protected static $targets = array();
    protected static $closed = false;
    
    public static function setUp(array $config)
    {
        if(!isset($config['targets']))
            return;
        foreach($config['targets'] as $target) {
            $t = null;
            // TODO: DYNAMIC!
            switch ($target['type']) {
                case 'file':
                    $t = new FileLogger($target['path']);
                    break;
                case 'echo':
                    $t = new EchoLogger();
                    break;
            }
            
            if($t != null) {
                self::$targets[] = $t;
            }
        }
    }
    
    public static function write($msg)
    {
        foreach(self::$targets as $t) 
        {
            if($t instanceof Logger)
                $t->write($msg);
        }
    }
    
    public static function close()
    {
        foreach(self::$targets as $t)
        {
            if($t instanceof Logger)
                $t->close ();
        }
    }
}

?>
