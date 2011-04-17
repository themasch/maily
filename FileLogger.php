<?php
require_once './Logger.php';
/**
 * 
 * @author masch
 */
class FileLogger implements Logger {
    
    protected $fp;
    
    public function __construct($path)
    {
        if(!is_writable($path)) {
            throw new RuntimeException('logtarget "'.$path.'" is not writeable');
        }
        $fp = fopen($path, 'a');
    }

    public function write($msg)
    {
        if(!is_string($msg)) {
            ob_start();
            var_dump($msg);
            $msg = ob_get_clean();
        }
        fwrite($this->fp, '['.date('d.m.Y H:i:s').']'.$msg);
    }
    
    public function close()
    {
        fclose($fp);
    }
}