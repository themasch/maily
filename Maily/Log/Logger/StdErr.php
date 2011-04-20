<?php

namespace Maily\Log\Logger;
use Maily\Log;

require_once  BASE.'/Logger.php';
/**
 * @author Mark Schmale <masch@masch.it>
 */
class StdErr implements Logger {

    public function write($msg)
    {
        if(!is_string($msg))
            $msg = print_r($msg, true);

        file_put_contents("php://stderr", $msg, FILE_APPEND);

    }

    public function close() {}
}
