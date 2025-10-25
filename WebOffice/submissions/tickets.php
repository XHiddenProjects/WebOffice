<?php
use WebOffice\URI,
WebOffice\Security,
WebOffice\Users;
#header('Content-Type: application/json; charset=utf-8');
$p = dirname(__DIR__).'/libs';
foreach(array_diff(scandir($p),['.','..']) as $f) if(is_file("$p/$f")) include_once "$p/$f";

function addDigit(string $ticketID): string {
    $split = explode('-', $ticketID);
    if (count($split) !== 2) return $ticketID;
    $prefix = $split[0];
    $numberStr = $split[1];
    // Convert the last part to integer, add 1
    $number = intval($numberStr);
    $number += 1;
    // Preserve leading zeros, assuming original length
    $newNumberStr = str_pad($number, strlen($numberStr), '0', STR_PAD_LEFT);
    // Reconstruct the ticket ID
    return "$prefix-$newNumberStr";
}

$security = new Security();
$users = new Users();
$uri = new URI();

$subject = $security->preventXSS($_POST['ticket-subject']);
$description = $security->preventXSS($_POST['ticket-description']);
$attachments = $_FILES['ticket-attachments'];

$has_files = false;

for ($i = 0; $i < count($attachments['name']); $i++) {
    if ($attachments['error'][$i] === UPLOAD_ERR_OK && $attachments['size'][$i] > 0) {
        $has_files = true;
        break;
    }
}

$main = $security->preventXSS($security->sanitize(base64_decode($_POST['main']),$security::SANITIZE_URL));
if($has_files)
    $attachURL = array_map(fn($item): mixed =>$item['destination'],$security->upload($attachments));
$api = curl_init();
$url = "$main/api/tickets";
curl_setopt($api,CURLOPT_URL,$url);
curl_setopt($api,CURLOPT_CUSTOMREQUEST,'GET');
curl_setopt($api, CURLOPT_RETURNTRANSFER, true);
$response = json_decode(curl_exec($api),true);
if(empty($response)){
    $ticketID = "TKT-0001";
    $userID = $users->getID();
    $date = date('Y-m-d H:i:s');
    $priority = 'low';
    $status = 'open';
}