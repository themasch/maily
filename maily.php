#!/bin/env php
<?php
define('BASE', __DIR__);
require_once BASE.'/Maily.php';

define('VERSION', '0.1');

$sender = trim(isset($argv[1]) ? $argv[1] : "");
$list   = trim(isset($argv[2]) ? $argv[2] : "");

$maily = new Maily('/opt/maily/maily.cfg');
if($sender == '--transport-map' || $sender == '-m') {
    $maily->generateTransportMap($list);
} else {
    if($sender == null || $list == null || $sender == '' || $list == '') {
    	Log::write('no from || no to', Log::ERR);
    }
    $msg = file_get_contents('php://stdin');
    $maily->handleMail($sender, $list, $msg);
}


