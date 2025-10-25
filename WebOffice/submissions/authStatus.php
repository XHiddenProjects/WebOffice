<?php
# Checks updates the user's last activity timestamp and active/inactive status
use WebOffice\Security, WebOffice\Users, WebOffice\Locales, WebOffice\Storage;
header('Content-Type: application/json; charset=utf-8');
$p = dirname(__DIR__).'/libs';
include_once dirname(__DIR__).'/init.php';
foreach(array_diff(scandir($p),['.','..']) as $f) if(is_file("$p/$f")) include_once "$p/$f";
$security = new Security();
$users = new Users();
$lang= new Locales(implode('-',LANGUAGE));
$storage = new Storage();
$auth = $storage->cookie('weboffice_auth', action: 'load') ?? $storage->session('weboffice_auth', action: 'get');
$path = $security->preventXSS($_GET['path']);
if ($auth) {
    $username = base64_decode($auth);
    // Update last_activity timestamp for current user
    $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "$path/api/users?".http_build_query([
                'username' => $username,
                'status' => 'active',
                'last_activity' => date('Y-m-d H:i:s',time())
            ]));
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
    echo json_encode(['status' => 'success', 'msg'=>$result??null], JSON_UNESCAPED_SLASHES);
}else {
    // Get all users and check their last_activity even if no one is logged in
    $usersList = $users->list();
    $inactiveThreshold = 1; // 1 seconds
    foreach ($usersList as $user) {
        $lastActivity = $users->getLastActivity($user['username']);
        if ($lastActivity !== null && (time() - $lastActivity) > $inactiveThreshold) {
            // Set user status to offline via CURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "$path/api/users?".http_build_query([
                'username' => $user['username'],
                'status' => 'inactive'
            ]));
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
        }
    }
    echo json_encode(['status'=>'success','msg'=>$result??null], JSON_UNESCAPED_SLASHES);
}