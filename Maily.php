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
require_once __DIR__.'/Maily/DB.php';
require_once __DIR__.'/Maily/Log.php';
require_once __DIR__.'/Maily/List.php';
require_once __DIR__.'/Maily/Config.php';
require_once __DIR__.'/Maily/Parser.php';
require_once __DIR__.'/Maily/Transport/SMTP.php';


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
        $cfg = Maily\Config::readConfig($path);
        Maily\Log::setUp($cfg['log']);
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
                Maily\Log::write('file "'.$file.'" is not readable'.PHP_EOL, Maily\Log::ERROR);
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
            Maily\Log::write('['.$list->__toString().'] sender not authorized: "'.$from.'"', Maily\Log::ERROR);
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
            Maily\Log::write('['.$list->__toString().'] mail from "'.$from.'" has been sent to the list');
        }
        catch(Exception $e) {
            Maily\Log::write($e->getMessage(), Maily\Log::ERROR);
            Maily\Log::write($e->getTraceAsString(), Maily\Log::ERROR);
        }
        
    }
}
