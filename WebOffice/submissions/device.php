<?php
use WebOffice\Security,
WebOffice\Locales,
WebOffice\Device;
header('Content-Type: application/json; charset=utf-8');
$p = dirname(__DIR__).'/libs';
foreach(array_diff(scandir($p),['.','..']) as $f) if(is_file("$p/$f")) include_once "$p/$f";

$devices = new Device();
$locales = new Locales(implode('-',LANGUAGE));
$security = new Security();

if(isset($_GET['action'])){
    switch(strtolower($_GET['action'])){
        case 'add':
            if($security->CSRF('verify',$_POST['token'])){
                $insertDevice = $devices->addDevice($_POST['deviceName'],
            $_POST['deviceType'],
        $_POST['deviceBrand'],
    $_POST['deviceModel'],
$_POST['deviceOS'],
$_POST['deviceSerial'],
$_POST['deviceManufacturer'],
$_POST['deviceLocation'],
$_POST['deviceIP'],
strtotime($_POST['purchaseDate']),
strtotime($_POST['warrantyExpiry']),
'deactivate',
$_POST['deviceAsset']);
                if($insertDevice){
                    $security->CSRF(action: 'generate', forceGenerate: true);
                    echo json_encode(['success'=>true],JSON_UNESCAPED_SLASHES);
                }else echo json_encode(['success'=>false, 'msg'=>$locales->load(['errors','deviceExists'])],JSON_UNESCAPED_SLASHES);
            }else echo json_encode(['success'=>false, 'msg'=>$locales->load(['errors','csrfInvalid'])],JSON_UNESCAPED_SLASHES);
        break;
    }
}