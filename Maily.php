<?php
require_once BASE.'/DB.php';
require_once BASE.'/Log.php';
require_once BASE.'/List.php';
require_once BASE.'/Config.php';
require_once BASE.'/Maily/Parser.php';
require_once BASE.'/Maily/Transport/SMTP.php';

class Maily 
{

    protected $version = '0.2';

    public function  __construct($path) {
        $cfg = Config::readConfig($path);
        Log::setUp($cfg['log']);
    }

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
        $lists = mList::getAll(); // read this from db
        $txt = '';
        foreach($lists as $m) {
            $txt .= $m['address'].' maily'.PHP_EOL;
        }
        $raw = preg_replace('('.$start.'(.*)'.$end.')i', $start.$txt.$end, $raw);
    }

    public function handleMail($from, $to, $msg)
    {
        $list = mList::lookUp($to);

        if(!$list->canSend($from)) {
            Log::write('['.$list->__toString().'] sender not authorized: "'.$from.'"', Log::ERROR);
            return false;
        }

        try {
            $p = new Parser();
            $p->setContent($msg);
            $msg = $p->parse();

            $keep = array('from', 'content-type', 'subject', 'mime-version');
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

            $t = new Maily\Transport\SMTP();
            $t->connect();
            $t->send($msg, $targets, $from);
            $t->disconnect();
            Log::write('['.$list->__toString().'] mail from "'.$from.'" has been sent');
        }
        catch(Exception $e) {
            Log::write($e->getMessage(), Log::ERROR);
            Log::write($e->getTraceAsString(), Log::ERROR);
        }
        
    }
}
