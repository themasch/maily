<?php
/**
 * the Maily mailing list system
 *
 * LICENSE:
 *
 * As long as you retain this notice you can do whatever you want with this
 * stuff. If we meet some day, and you think this stuff is worth it, you can
 * buy me a beer in return.
 *
 * @author  Mark Schmale <masch@masch.it>
 * @license Beerware
 * @package Maily
 * @version 0.1
 * @filesource
 */

namespace Maily\Transport;
require_once __DIR__.'/../Transport.php';

/**
 * smtp mail transports
 *
 * @author  Mark Schmale <masch@masch.it>
 * @package Maily
 * @version 0.1
 */
class SMTP implements \Maily\Transport
{

    protected $host = 'localhost';

    protected $port = '25';

    protected $con  = null;

    protected $domain = 'localhost';
    
    public function __construct()
    {
    }

    public function connect()
    {
        $this->con = fsockopen($this->host, $this->port);
    }


    public function disconnect()
    {
        fclose($this->con);
    }

    public function send(\MessagePart $send, array $to, $from)
    {
        try {
            $this->readResponse();
            $this->execCommand('HELO '.$this->domain, array('250'));
            $this->execCommand('MAIL FROM: '.$from, array('250'));
            foreach($to as $addr) {
                $this->execCommand('RCPT TO: '.$addr, array('250'));
            }
            $this->execCommand('DATA', array('354'));
            $this->execCommand($send->__toString()."\r\n.\r\n", array('250'));
            $this->execCommand('QUIT', array('221'));
        }
        catch(\RuntimeException $ex) {
            \Log::write($ex->getMessage(), \Log::ERROR);
        }

    }

    protected function execCommand($cmd, array $codes)
    {
        $this->sendCommand($cmd);
        $resp = $this->readResponse();
	\Log::write(is_array($resp) ? 'resp okay' : 'resp failed');
        $line = array_pop($resp);
        if(in_array(substr($line, 0,3), $codes)) {
	    \Log::write('resp is expected');
            return true;
        } else {
            throw new \RuntimeException('invalid return: '.PHP_EOL. implode(PHP_EOL, $resp));
        }
    }

    protected function sendCommand($cmd)
    {
    	\Log::write('SENT COMMAND: '.$cmd);
        return fwrite($this->con, $cmd."\r\n");
    }

    protected function readResponse()
    {
        $resp = array();
        do {
            $line   = $this->readLine();
            $resp[] = $line;
        } while($line !== null && $line !== false && $line[3] !== ' ');
	\Log::write(gettype($resp));
	\Log::write($resp);
        return $resp;
    }

    protected function readLine()
    {
        $line = false;
//        if(!feof($this->con)) {
            $line = fgets($this->con);
 //       }
 	\Log::write('LINE: '.$line);
        return $line;
    }
}
?>
