<?php
use WebOffice\Security, WebOffice\Users, WebOffice\Locales, WebOffice\Storage, WebOffice\Database;
use WebOffice\Config;
header('Content-Type: application/json; charset=utf-8');
$p = dirname(__DIR__).'/libs';
foreach(array_diff(scandir($p),['.','..']) as $f) if(is_file("$p/$f")) include_once "$p/$f";


$security = new Security();
$users = new Users();
$lang= new Locales(implode('-',LANGUAGE));
$storage = new Storage();
$config = new Config();


if(!isset($_SESSION['temp_user'])) echo json_encode(['status'=>'error', 'msg'=>$lang->load(['errors','noAttemptLogin'])],JSON_UNESCAPED_SLASHES);
else{
    $remember = $_SESSION['temp_remember']??false;
    $tempUser = $_SESSION['temp_user'];

    $code = $security->sanitize($_POST['mfa_code'], $security::SANITIZE_INT);

    $secrete = (new Database(
        $config->read('mysql','host'),
        $config->read('mysql','user'),
        $config->read('mysql','psw'),
        $config->read('mysql','db')
    ))->fetch("SELECT 2fa_secret FROM mfa WHERE username=:user",['user'=>$tempUser]);


    if($security->MFA($secrete['2fa_secret'],$code, 'VERIFY')){
        if($remember) $storage->cookie('weboffice_auth',base64_encode($res[0]['username']),'store',720);    
        else $storage->session('weboffice_auth',base64_encode($res[0]['username']));
        $url = "$main/api/users?username={$tempUser}&status=active&last_login=".date('Y-m-d H:i:s',time())."&last_activity=".date('Y-m-d H:i:s',time());
                        $api = curl_init();
                        curl_setopt($api,CURLOPT_URL,$url);
                        curl_setopt($api,CURLOPT_CUSTOMREQUEST,'PUT');
                        curl_setopt($api, CURLOPT_RETURNTRANSFER, true);
                        $result = curl_exec($api);
                        if(curl_errno($api)) echo json_encode(['status'=>'error','msg'=>'cURL Error: ' . curl_error($api)],JSON_UNESCAPED_SLASHES);
                        curl_close($api);
        echo json_encode(['status'=>'success']);
    }else echo json_encode(['status'=>'error','msg'=>$lang->load(['errors','invalidMFA'])],JSON_UNESCAPED_SLASHES);
}