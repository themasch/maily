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
 * @version 0.2
 * @filesource
 */
require_once BASE.'/DB.php';
require_once BASE.'/Log.php';
require_once BASE.'/List.php';
require_once BASE.'/Config.php';
require_once BASE.'/Maily/Parser.php';
require_once BASE.'/Maily/Transport/SMTP.php';

/**
 * main class of maily
 * 
 * @author Mark Schmale
 * @package Maily
 */
class Maily 
{

    protected $version = '0.2';

    public function  __construct($path) {
        $cfg = Config::readConfig($path);
        Log::setUp($cfg['log']);
    }

    /**
     * creates a transport map for postfix
     * @param string $path path of an existing map
     */
    public function generateTransportMap($path=null)
    {
        $start = '########## Maily start ##########'.PHP_EOL;
        $end   = '########## Maily end ############'.PHP_EOL;
        $raw = $start.' '.$end;
        if($file != '') {
            if(is_readable($file)) 
                $raw = file_get_contents($file); 
            else 
                Log::write('file "'.$file.'" is not readable'.PHP_EOL, Log::ERROR);
        }
        $lists = mList::getAll();
        $txt = '';
        foreach($lists as $m) {
            $txt .= $m['address'].' maily;'.PHP_EOL;
        }
        $raw = preg_replace('('.$start.'(.*)'.$end.')i', $start.$txt.$end, $raw);
    }

    /**
     *
     * @param string $from the senders mail address
     * @param string $to   the lists mail address
     * @param string $msg  the mails content
     * @return boolean 
     */
    public function handleMail($from, $to, $msg)
    {
        $list = Maily\ListModel::lookUp($to);

        if(!$list->canSend($from)) {
            Log::write('['.$list->__toString().'] sender not authorized: "'.$from.'"', Log::ERROR);
            return false;
        }

        try {
            $p = new Maily\Parser();
            $p->setContent($msg);
            $msg = $p->parse();

	    $keep = array(  
                            'from', 
                            'content-type', 
                            'subject', 
                            'mime-version', 
                            'content-transfer-encoding'
                         );
            $msg->clearHeader($keep);
            $msg->setHeader('to', $list->__toString());

            $prfx = $list->getConfig('subject_prefix', false);
            if($prfx !== false) {
                $subject = (string)$msg->getHeader('subject');
                $subject = $prfx.' '.$subject;
                $msg->setHeader('subject', $subject);
            }
            $msg->setHeader('reply-to', $list->__toString());
            $msg->setHeader('x-mailer', 'Maily v'.$this->version);

            $targets = $list->getTargets();
            
            // don't send a message to the senders
            foreach($targets as $k => $target) {
                if($target == $from) {
                    unset($targets[$k]);
                }
            }

            // send the mail
            $t = new Maily\Transport\SMTP();
            $t->connect();
            $t->send($msg, $targets, $from);
            $t->disconnect();
            Log::write('['.$list->__toString().'] mail from "'.$from.'" has been sent to the list');
        }
        catch(Exception $e) {
            Log::write($e->getMessage(), Log::ERROR);
            Log::write($e->getTraceAsString(), Log::ERROR);
        }
        
    }
}
