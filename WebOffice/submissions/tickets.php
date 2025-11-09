<?php
use WebOffice\Security,
WebOffice\Users,
WebOffice\Locales;
header('Content-Type: application/json; charset=utf-8');
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
$locales = new Locales(implode('-',LANGUAGE));


if(isset($_GET['action'])){
    switch(strtolower($_GET['action'])){
        case 'create':
            $subject = $security->preventXSS($_POST['ticket-subject']);
            $description = $security->preventXSS($_POST['ticket-description']);
            $attachments = $_FILES['ticket-attachments'];
            $issueCategory = $security->preventXSS($_POST['ticket-category']);

            $has_files = false;

            for ($i = 0; $i < count($attachments['name']); $i++) {
                if ($attachments['error'][$i] === UPLOAD_ERR_OK && $attachments['size'][$i] > 0) {
                    $has_files = true;
                    break;
                }
            }

            $main = $security->preventXSS($security->sanitize(base64_decode($_POST['main']),$security::SANITIZE_URL));
            if ($has_files) {
                $uploads = $security->upload($attachments);
                $attachments = [];
                foreach ($uploads as $file) {
                    array_push($attachments,$file['destination']);
                }
            }else $attachments = [];
            $api = curl_init();
            $url = "$main/api/tickets";
            curl_setopt($api,CURLOPT_URL,$url);
            curl_setopt($api,CURLOPT_CUSTOMREQUEST,'GET');
            curl_setopt($api, CURLOPT_RETURNTRANSFER, true);
            $response = json_decode(curl_exec($api),true);
            curl_close($api);

            if(empty($response)){
                $api = curl_init();
                $url ="$main/api/tickets?".http_build_query([
                    'ticket_id'=>'TKT-0001',
                    'user_id'=>$users->getID(),
                    'subject'=>$subject,
                    'description'=>$description,
                    'priority'=>'low',
                    'status'=>'open',
                    'category'=>$issueCategory,
                    'attachments'=>json_encode($attachments,JSON_UNESCAPED_SLASHES)
                ]);
                curl_setopt($api,CURLOPT_URL,$url);
                curl_setopt($api,CURLOPT_POST,true);
                curl_setopt($api, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($api);
                // Check for errors
                if(curl_errno($api)) echo 'cURL Error: ' . curl_error($api);    
                // Close cURL session
                curl_close($api);
                echo json_encode(['success'=>$response],JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
            }else{
                $api = curl_init();
                $url ="$main/api/tickets?".http_build_query([
                    'ticket_id'=>addDigit(end($response)['ticket_id']),
                    'user_id'=>$users->getID(),
                    'subject'=>$subject,
                    'description'=>$description,
                    'priority'=>'low',
                    'status'=>'open',
                    'category'=>$issueCategory,
                    'attachments'=>json_encode($attachments,JSON_UNESCAPED_SLASHES)
                ]);
                curl_setopt($api,CURLOPT_URL,$url);
                curl_setopt($api,CURLOPT_POST,true);
                curl_setopt($api, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($api);
                // Check for errors
                if(curl_errno($api)) echo 'cURL Error: ' . curl_error($api);    
                // Close cURL session
                curl_close($api);
                echo json_encode(['success'=>$response],JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
            }
        break;
        case 'save':
            $main = base64_decode($security->preventXSS($_POST['main']));
            $token = $security->preventXSS($_POST['token']);
            $ticketId = $security->preventXSS($_POST['ticket_id']);
            $priority = $security->preventXSS($_POST['ticket-priority']);
            $status = $security->preventXSS($_POST['ticket-status']);
            $assigned = explode(',',preg_replace('/,$/','',$security->preventXSS(trim($_POST['assigned_to']))));
            $assigned = array_filter($assigned,fn($i): bool=>$i!=='');
            if($security->CSRF('verify',$token)){
                $api = curl_init();
                $query = http_build_query([
                    'ticket_id' => $ticketId,
                    'priority'=>$priority,
                    'status' => $status,
                    'assigned_to' => json_encode($assigned,JSON_UNESCAPED_SLASHES),
                    'updated_at' => date('Y-m-d H:i:s', time())
                ]);
                $url ="$main/api/tickets?$query";
                curl_setopt($api, CURLOPT_URL, $url);
                curl_setopt($api, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($api, CURLOPT_RETURNTRANSFER, true);
                $result = curl_exec($api);
                if(curl_errno($api)) echo json_encode(['status'=>'error','msg'=>'cURL Error: ' . curl_error($api)],JSON_UNESCAPED_SLASHES);
                else{
                    $security->CSRF(action: 'generate',forceGenerate: true);
                    echo json_encode(['success'=>true],JSON_UNESCAPED_SLASHES);
                }
                curl_close($api);
            }else 
                echo json_encode(['success'=>false,'msg'=>$locales->load(['errors','csrfInvalid'])]);
        break;
    }
}