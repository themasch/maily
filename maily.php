#!/bin/env php
<?php
//error_reporting(E_ERROR | E_WARNING | E_PARSE);
require_once __DIR__.'/Maily.php';

define('VERSION', '0.1');

$sender = trim(isset($argv[1]) ? $argv[1] : "");
$list   = trim(isset($argv[2]) ? $argv[2] : "");

$maily = new Maily(__DIR__.'/maily.cfg');
Maily\Log::write('startup');
Maily\Log::write($argv);
if($sender == '--transport-map' || $sender == '-m') {
    $maily->generateTransportMap($list);
} else {
    if($sender == null || $list == null || $sender == '' || $list == '') {
    	Maily\Log::write('no from || no to', Maily\Log::ERROR);
        die;
    }
    $msg = file_get_contents('php://stdin');
    $maily->handleMail($sender, $list, $msg);
}


