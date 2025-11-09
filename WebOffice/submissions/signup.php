<?php
use WebOffice\URI,WebOffice\Security, WebOffice\Locales, WebOffice\Device,WebOffice\Users;
header('Content-Type: application/json; charset=utf-8');
$p = dirname(__DIR__).'/libs';
foreach(array_diff(scandir($p),['.','..']) as $f) if(is_file("$p/$f")) include_once "$p/$f";


$security = new Security();
$users = new Users();
$lang= new Locales(htmlspecialchars($_POST['language']));
$uri = new URI();
$device = new Device();

$token = $security->preventXSS($_POST['token']);
$fname = $security->preventXSS($security->sanitize($_POST['fname']));
$mname = $security->preventXSS($security->sanitize($_POST['mname']));
$lname = $security->preventXSS($security->sanitize($_POST['lname']));
$username = $security->preventXSS($security->sanitize($_POST['username']));
$email = $security->preventXSS($security->filter($_POST['email'], $security::FILTER_EMAIL));
$language = $security->preventXSS($security->sanitize($_POST['language']));
$main = $security->preventXSS($security->sanitize(base64_decode($_POST['main']),$security::SANITIZE_URL));
$psw = trim($_POST['password']);
$c_psw = trim($_POST['confirm_password']);
$timezone = htmlspecialchars($_POST['timezone']);
if(!$security->filter($email,$security::FILTER_EMAIL)) echo json_encode(['status'=>'error','msg'=>$lang->load(['errors','invalidEmail'])],JSON_UNESCAPED_SLASHES);
else{
    if($psw!==$c_psw) echo json_encode(['status'=>'error', 'msg'=>$lang->load(['errors','mismatch_psw'])],JSON_UNESCAPED_SLASHES);
    else{
        if($security->CSRF('verify',$token)){
            $adminExists=false;
            
            # check if admin account exists
            $api = curl_init();
            $url = "$main/api/users?sel=permission=admin";
            curl_setopt($api,CURLOPT_URL,$url);
            curl_setopt($api,CURLOPT_CUSTOMREQUEST,'GET');
            curl_setopt($api, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($api);

            // Check for errors
            if(curl_errno($api)){
                echo 'cURL Error: ' . curl_error($api);
            }

            // Close cURL session
            curl_close($api);
            $res = json_decode($response,true);
            if(!empty($res)&&!isset($res)) $adminExists = true;

            # Check if user exists

            $api = curl_init();
            $url = "$main/api/users?sel=username=$username||email=$email";
            curl_setopt($api,CURLOPT_URL,$url);
            curl_setopt($api,CURLOPT_CUSTOMREQUEST,'GET');
            curl_setopt($api, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($api);

            // Check for errors
            if(curl_errno($api)){
                echo 'cURL Error: ' . curl_error($api);
            }

            // Close cURL session
            curl_close($api);
            $res = json_decode($response,true);
            if(!empty($res)&&!isset($res)) echo json_encode(['status'=>'error','msg'=>$lang->load(['errors','UsernameOrEmailExists'])],JSON_UNESCAPED_SLASHES);
            else{

                $api = curl_init();
                $url = "$main/api/users?".http_build_query([
                    'first_name'=>$fname, 
                    'middle_name'=>$mname, 
                    'last_name'=>$lname, 
                    'username'=>$username,
                    'email'=>$email,
                    'password'=>$security->hashPsw($psw,PASSWORD_BCRYPT,['cost'=>14]),
                    'ip_address'=>$users->getIP(),
                    'user_agent'=>$device->getUserAgent(),
                    'status'=>'active',
                    'permissions'=>!$adminExists ? 'admin' : 'member',
                    'language'=>$language,
                    'timezone'=>$timezone
                ]);
                curl_setopt($api,CURLOPT_URL,$url);
                curl_setopt($api,CURLOPT_POST,true);
                curl_setopt($api, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($api);

                // Check for errors
                if(curl_errno($api)) echo 'cURL Error: ' . curl_error($api);
                

                // Close cURL session
                curl_close($api);
                $res = json_decode($response,true);
                if(empty($res)&&!isset($res))
                    echo json_encode(['status'=>'error','msg'=>$lang->load(['errors','failedCreatedUser'])],JSON_UNESCAPED_SLASHES);
                else{
                    $security->CSRF(action: 'generate',forceGenerate: true);
                    $storage->session('weboffice_auth',base64_encode($username));
                    echo json_encode(['status'=>'success', 'msg'=>$res],JSON_UNESCAPED_SLASHES);
                }
            }
        }else echo json_encode(['status'=>'error','msg'=>$lang->load(['errors','csrfInvalid'])],JSON_UNESCAPED_SLASHES);
    }
}
