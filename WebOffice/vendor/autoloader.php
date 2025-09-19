<?php
$l = array_merge(array_values(array_filter(array_diff(scandir(dirname(__DIR__).'/libs'), ['.', '..']), fn($entry)=>is_file(dirname(__DIR__).'/libs/' . $entry))));
$a = array_merge(array_values(array_filter(array_diff(scandir(dirname(__DIR__).'/addons'), ['.', '..']), fn($entry)=>is_file(dirname(__DIR__).'/addons/' . $entry))));
foreach($l as $ls){
    if(is_file(dirname(__DIR__)."/libs/$ls")) include_once dirname(__DIR__)."/libs/$ls";
}
foreach($a as $as) include_once dirname(__DIR__)."/addons/$as/$as.plg.php";

include_once dirname(__DIR__).'/libs/device-detector/Spyc.php';
include_once dirname(__DIR__).'/libs/PHPMailer/src/Exception.php';
include_once dirname(__DIR__).'/libs/PHPMailer/src/PHPMailer.php';
include_once dirname(__DIR__).'/libs/PHPMailer/src/SMTP.php';
include_once dirname(__DIR__).'/libs/PHPMailer/src/POP3.php';

include_once dirname(__DIR__).'/libs/device-detector/autoload.php';
