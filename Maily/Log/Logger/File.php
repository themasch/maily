<?php

namespace Maily\Log\Logger;
use Maily\Log\Logger;

require_once __DIR__.'/../Logger.php';
/**
 * 
 * @author Mark Schmale <masch@masch.it>
 */
class File implements Logger {
    
    protected $fp;
    
    public function __construct($path)
    {
        if(!is_writable($path)) {
            throw new \RuntimeException('log file "'.$path.'" is not writeable');
        }
        $this->fp = @fopen($path, 'a');
        if($this->fp === false) {
            throw new \RuntimeException('can`t open log file: '.$path);
        }
    }

    public function write($msg)
    {
        if($this->fp === false) return;
        if(!is_string($msg)) {
            ob_start();
            var_dump($msg);
            $msg = ob_get_clean();
        }
        $lines = explode("\n", $msg);
        foreach($lines as $line) {
            fwrite($this->fp, '['.date('d.m.Y H:i:s').']'.$line.PHP_EOL);
        }
    }
    
    public function close()
    {
        fclose($this->fp);
    }
}
