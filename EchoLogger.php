<?php
require_once './Logger.php';
/**
 * Description of EchoLogger
 *
 * @author masch
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
