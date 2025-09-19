<?php
use WebOffice\Security, WebOffice\Users, WebOffice\Locales, WebOffice\Storage;
header('Content-Type: application/json; charset=utf-8');
$p = dirname(__DIR__).'/libs';
foreach(array_diff(scandir($p),['.','..']) as $f) if(is_file("$p/$f")) include_once "$p/$f";

$security = new Security();
$users = new Users();
$lang= new Locales(implode('-',LANGUAGE));
$storage = new Storage();

$token = $security->preventXSS($_POST['token']);
$username = $security->preventXSS($security->sanitize($_POST['username']));
$password = $_POST['password'];
$remember = isset($_POST['remember']) ? true : false;
$main = $security->preventXSS($security->sanitize(base64_decode($_POST['main']),$security::SANITIZE_URL));

if($security->CSRF('verify',$token)){
    $api = curl_init();
    $url = "$main/api/users?sel=username=$username||email=$username";
    curl_setopt($api,CURLOPT_URL,$url);
    curl_setopt($api,CURLOPT_CUSTOMREQUEST,'GET');
    curl_setopt($api, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($api);
    if(curl_errno($api)) echo json_encode(['status'=>'error','msg'=>'cURL Error: ' . curl_error($api)],JSON_UNESCAPED_SLASHES);
    else{
        curl_close($api);
        $res = json_decode($response,true);
        if(!empty($res)){
            if(password_verify($password,$res[0]['password'])){
                if($res[0]['2fa_enabled']){
                    $_SESSION['temp_user'] = $res[0]['username'];
                    $_SESSION['temp_remember'] = $remember;
                    $msg = ['2fa'=>true, 'logon_session'=>!$remember?true:false];
                }else{
                    $url = "$main/api/users?username={$res[0]['username']}&status=active&last_login=".date('Y-m-d H:i:s',time())."&last_activity=".date('Y-m-d H:i:s',time());
                    $api = curl_init();
                    curl_setopt($api,CURLOPT_URL,$url);
                    curl_setopt($api,CURLOPT_CUSTOMREQUEST,'PUT');
                    curl_setopt($api, CURLOPT_RETURNTRANSFER, true);
                    $result = curl_exec($api);
                    if(curl_errno($api)) echo json_encode(['status'=>'error','msg'=>'cURL Error: ' . curl_error($api)],JSON_UNESCAPED_SLASHES);
                    curl_close($api);
                    if($remember) $storage->cookie('weboffice_auth',base64_encode($res[0]['username']),'store',720);    
                    else $storage->session('weboffice_auth',base64_encode($res[0]['username']));
                }
                
                echo json_encode(['status'=>'success','msg'=>$msg??''],JSON_UNESCAPED_SLASHES);
            }else echo json_encode(['status'=>'error','msg'=>$lang->load(['errors','incorrectPassword'])],JSON_UNESCAPED_SLASHES);;
        }else echo json_encode(['status'=>'error','msg'=>$lang->load(['errors','noUser'])],JSON_UNESCAPED_SLASHES);
    }  
}else echo json_encode(['status'=>'error','msg'=>$lang->load(['errors','csrfInvalid'])],JSON_UNESCAPED_SLASHES);         