<?php
use WebOffice\Users, WebOffice\Security, WebOffice\Storage;
$p = dirname(__DIR__).'/libs';
foreach(array_diff(scandir($p),['.','..']) as $f) if(is_file("$p/$f")) include_once "$p/$f";
header('Content-Type: application/json; charset=utf-8');

$security = new Security();
$users = new Users();
$storage = new Storage();

if($storage->cookie('weboffice_auth',action:'load')){
    $storage->cookie('weboffice_auth',action: 'delete');
}else if($storage->session('weboffice_auth',action:'get')){
    $storage->session('weboffice_auth',action: 'delete');
}else {
    echo json_encode(['status'=>'error','msg'=>'No active session found'],JSON_UNESCAPED_SLASHES);
    exit;
}
echo json_encode(['status'=>'success','msg'=>'Logged out successfully'],JSON_UNESCAPED_SLASHES);
exit;