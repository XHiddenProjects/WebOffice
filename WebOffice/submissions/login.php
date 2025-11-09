<?php
use WebOffice\Security, WebOffice\Users, WebOffice\Locales, WebOffice\Storage, WebOffice\Config, WebOffice\Database, WebOffice\Device;
header('Content-Type: application/json; charset=utf-8');
$p = dirname(__DIR__).'/libs';
foreach(array_diff(scandir($p),['.','..']) as $f) if(is_file("$p/$f")) include_once "$p/$f";

$security = new Security();
$users = new Users();
$lang= new Locales(implode('-',LANGUAGE));
$storage = new Storage();
$config = new Config();
$db = new Database(
    $config->read('mysql','host'),
    $config->read('mysql','user'),
    $config->read('mysql','psw'),
    $config->read('mysql','db')
);

$device = new Device();

$failedAttempt=false;

$token = $security->preventXSS($_POST['token']);
$username = $security->preventXSS($security->sanitize($_POST['username']));
$password = $_POST['password'];
$remember = isset($_POST['remember']) ? true : false;
$main = $security->preventXSS($security->sanitize(base64_decode($_POST['main']),$security::SANITIZE_URL));

$fetchedAttempts = $db->fetch("SELECT * FROM attempts WHERE ip_address=:ip", ['ip'=>$users->getIP()], PDO::FETCH_ASSOC)??[];


# reset attempts if time attempted_at is over a 15 minutes duration
if(isset($fetchedAttempts)&&!empty($fetchedAttempts)&&$config->read('security','psw_attempts')>0){
    if(strtotime($fetchedAttempts['attempted_at'])<(time()-900)){
        $db->delete('attempts',["ip_address"=>$users->getIP()]);
        $fetchedAttempts = null;
    }
    if(isset($fetchedAttempts)&&!empty($fetchedAttempts)){
        if($fetchedAttempts['attempt']>=$config->read('security','psw_attempts')){
            echo json_encode(['status'=>'error','msg'=>$lang->load(['errors','tooManyAttempts'])],JSON_UNESCAPED_SLASHES);
            exit;
        }
    }
}

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
                    $query = http_build_query([
                        'username' => $res[0]['username'],
                        'status' => 'active',
                        'last_login' => date('Y-m-d H:i:s', time()),
                        'last_activity' => date('Y-m-d H:i:s', time())
                    ]);
                    $url = "$main/api/users?$query";
                    $api = curl_init();
                    curl_setopt($api, CURLOPT_URL, $url);
                    curl_setopt($api, CURLOPT_CUSTOMREQUEST, 'PUT');
                    curl_setopt($api, CURLOPT_RETURNTRANSFER, true);
                    $result = curl_exec($api);
                    if(curl_errno($api)) echo json_encode(['status'=>'error','msg'=>'cURL Error: ' . curl_error($api)],JSON_UNESCAPED_SLASHES);
                    curl_close($api);
                    if($remember) $storage->cookie('weboffice_auth',base64_encode($res[0]['username']),'store',720);    
                    else $storage->session('weboffice_auth',base64_encode($res[0]['username']));
                }
                $security->CSRF(action: 'generate', forceGenerate: true);
                echo json_encode(['status'=>'success','msg'=>$msg??''],JSON_UNESCAPED_SLASHES);
                # delete attempt
                if(isset($fetchedAttempts)&&!empty($fetchedAttempts)) $db->delete('attempts',['ip_address'=>$users->getIP()]);
            }else {
                $failedAttempt = true;
                echo json_encode(['status'=>'error','msg'=>$lang->load(['errors','incorrectPassword'])],JSON_UNESCAPED_SLASHES);
            }
        }else {
            $failedAttempt = true;
            echo json_encode(['status'=>'error','msg'=>$lang->load(['errors','noUser'])],JSON_UNESCAPED_SLASHES);
        }
        if($failedAttempt&&$config->read('security','psw_attempts')>0){
            # insert or update attempts
            if(isset($fetchedAttempts)&&!empty($fetchedAttempts)){
                $db->update('attempts',[
                    'attempt' => (int)$fetchedAttempts['attempt']+1,
                    'attempted_at' => date('Y-m-d H:i:s', time()),
                    'user_agent' => $device->getUserAgent()
                ],[
                    'ip_address' => $users->getIP()
                ]);
            }else{
                $db->insert('attempts',[
                    'ip_address' => $users->getIP(),
                    'attempt' => 1,
                    'attempted_at' => date('Y-m-d H:i:s', time()),
                    'user_agent' => $device->getUserAgent()
                ]);
            }
        }
    }  
}else echo json_encode(['status'=>'error','msg'=>$lang->load(['errors','csrfInvalid'])],JSON_UNESCAPED_SLASHES);         