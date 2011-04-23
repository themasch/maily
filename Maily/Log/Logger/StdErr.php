<?php

namespace Maily\Log\Logger;
use Maily\Log\Logger;

require_once  __DIR__.'/../Logger.php';
/**
 * @author Mark Schmale <masch@masch.it>
 */
class StdErr implements Logger {

    public function write($msg)
    {
        if(!is_string($msg)) {
            ob_start();
            var_dump($msg);
            $msg = ob_get_contents();
            ob_end_clean();   
        }
        file_put_contents("php://stderr", $msg, FILE_APPEND);

    }

    public function close() {}
}
