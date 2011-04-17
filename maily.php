#!/bin/env php
<?php
require_once './DB.php';
require_once './Log.php';
require_once './List.php';
require_once './Config.php';
require_once './Mail.php';

define('VERSION', '0.1');

$cfg = Config::readConfig('./maily.cfg');
$default_log = array('targets' => array(array('type' => 'echo')));


$sender = trim(isset($argv[1]) ? $argv[1] : "");
$list   = trim(isset($argv[2]) ? $argv[2] : "");

if($sender == '--transport-map') {
    $file = $list;
    $start = '########## Maily start ##########'.PHP_EOL;
    $end   = '########## Maily end ############'.PHP_EOL;
    $raw = $start.' '.$end;
    if($file != '') {
        if(is_readable($file)) 
            $raw = file_get_contents($file); 
        else 
            die('file "'.$file.'" is not readable'.PHP_EOL);
    }
    $lists = mList::getAll(); // read this from db
    $txt = '';
    foreach($lists as $m) {
        $txt .= $m['address'].' maily'.PHP_EOL;
    }
    $raw = preg_replace('('.$start.'(.*)'.$end.')i', $start.$txt.$end, $raw);
    echo $raw;
} else {
    Log::setUp(isset($cfg['log']) ? $cfg['log'] : $default_log);
    
    if($sender == '' || $list == '') {
        Log::write('please specify sender & list');
        die;
    }
    $laddr = $list;
    $list = mList::lookUp($list);
    
    if(!$list->canSend($sender)) {
        Log::write('sender is not allowed to send a mail to this list');
        die;
    }
    
    // parse the mail 
    $mail = new Mail();
    $mail->parse(file_get_contents('php://stdin'));
    
    if(!file_exists($cfg['archive_path'].'/'.$mail->getHash().'.eml')) {
        $mail->store($cfg['archive_path']);
    } else {
        // duplicate mail
        Log::write('duplicate mail');
        die;
    }

    // rewrite headers
    $mail->setHeader('To', (string)$list); 
    $mail->setHeader('Reply-To', (string)$list);

    $mail->setHeader('X-Mailer', 'Maily v'.VERSION);
    $mail->enableHeader('X-Mailer');
    
    // rewirte subject?
    if($list->getConfig('subject_prefix') != null) {
        $prfx = $list->getConfig('subject_prefix');
        $mail->setHeader('Subject', $prfx.' '.$mail->getHeader('Subject'));
    }

    $to = array();
    foreach($list->getTargets() as $t)  {
        if($t['address'] != $sender)
            $to[] = '<'.$t['address'].'>';
    }
    $bcc = implode(', ', $to);

    $mail->send($bcc);
}
