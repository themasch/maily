<?php

namespace Maily\Log\Logger;
use Maily\Log;

require_once BASE.'/Logger.php';
/**
 * 
 * @author masch
 */
class File implements Logger {
    
    protected $fp;
    
    public function __construct($path)
    {
        if(!is_writable($path)) {
            throw new RuntimeException('logtarget "'.$path.'" is not writeable');
        }
        $this->fp = fopen($path, 'a');
    }

    public function write($msg)
    {
        if(!is_string($msg)) {
            ob_start();
            var_dump($msg);
            $msg = ob_get_clean();
        }
        fwrite($this->fp, '['.date('d.m.Y H:i:s').']'.$msg.PHP_EOL);
    }
    
    public function close()
    {
        fclose($fp);
    }
}
