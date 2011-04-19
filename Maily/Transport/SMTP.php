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

require_once __DIR__.'/../Transport.php';

namespace Maily\Transport;

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
        $con = fsockopen($this->host, $this->port, &$errno, &$errstr);
    }


    public function disconnect()
    {
        fclose($con);
    }

    public function send(MessagePart $send, array $to, $from)
    {
        try {
            $this->readResponse();
            $this->sendCommand('HELO '.$this->domain, array('250'));
            $this->sendCommand('MAIL FROM: '.$from, array('250'));
            foreach($to as $addr) {
                $this->sendCommand('RCPT TO: '.$to, array('250'));
            }
            $this->sendCommand('DATA', array('354'));
            $this->sendCommand($send->__toString()."\r\n.\r\n");
            $this->sendCommand('QUIT', array('221'));
        }
        catch(\RuntimeException $ex) {
            Log::write($ex->getMessage(), Log::ERROR);
        }

    }

    protected function execCommand($cmd, array $codes)
    {
        $this->sendCommand($cmd);
        $resp = $this->readResponse();
        $line = array_pop($resp);
        if(in_array(substr($line, 0,3), $codes)) {
            return true;
        } else {
            throw new \RuntimeException('invalid return: '.PHP_EOL. implode(PHP_EOL, $resp));
        }
    }

    protected function sendCommand($cmd)
    {
        return fwrite($this->con."\r\n", $cmd);
    }

    protected function readResponse()
    {
        $resp = array();
        do {
            $line   = $this->readLine();
            $resp[] = $line;
        } while($line !== null && $line !== false && $line[3] !== ' ');
        return $resp;
    }

    protected function readLine()
    {
        $line = false;
        if(!feof($this->con)) {
            $line = fgets($this->con);
        }
        return $line;
    }
}
?>
