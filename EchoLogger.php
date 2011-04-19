<?php
require_once  BASE.'/Logger.php';
/**
 * @author Mark Schmale <masch@masch.it>
 */
class EchoLogger implements Logger {
    
    public function write($msg)
    {
        if(!is_string($msg))
            var_dump($msg);
        else 
            echo $msg.PHP_EOL;
    }
    
    public function close() {}
}
