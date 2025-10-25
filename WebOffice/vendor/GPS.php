<?php
header('Content-Type: application/json');
use WebOffice\Config;
use WebOffice\Database;
use WebOffice\Security;
use WebOffice\Device;
use WebOffice\Locales;
include_once dirname(__DIR__)."/libs/db.lib.php";
include_once dirname(__DIR__)."/libs/security.lib.php";
include_once dirname(__DIR__)."/libs/config.lib.php";
include_once dirname(__DIR__)."/libs/device.lib.php";
include_once dirname(__DIR__)."/libs/locales.lib.php";
$config = new Config("config",dirname(__DIR__)."/configuration");
$security = new Security();
$device = new Device();
$locales = new Locales(implode('-',LANGUAGE));
$db = new Database($config->read('mysql','host'),
$config->read('mysql','user'),
$config->read('mysql','psw'),
$config->read('mysql','db'));
$serial = $device->getSerial();


if($_SERVER['REQUEST_METHOD']==="POST"){
    $lat = (float)$security->filter($_POST['latitude'], Security::FILTER_FLOAT);
    $lon = (float)$security->filter($_POST['longitude'],Security::FILTER_FLOAT);
    $accuracy = (float)$security->filter($_POST['accuracy'],Security::FILTER_FLOAT);
    $altitude = (float)$security->filter($_POST['altitude'],Security::FILTER_FLOAT);
    $altitudeAccuracy = (float)$security->filter($_POST['altitudeAccuracy'],Security::FILTER_FLOAT);
    $speed = (float)$security->filter($_POST['speed'],Security::FILTER_FLOAT);
    $results = $db->fetch("SELECT * FROM gps_points WHERE serial_number=:serial_number",['serial_number'=>$serial]);
    if(empty($results)){
        $db->insert('gps_points',[
            'serial_number'=>$serial,
            'latitude'=>$lat,
            'longitude'=>$lon,
            'accuracy'=>$accuracy,
            'altitude'=>$altitude,
            'altitudeAccuracy'=>$altitudeAccuracy,
            'speed'=>$speed
        ]);
    }else{

        $d = $db->fetch("SELECT * FROM gps_points WHERE serial_number=:serial_number",['serial_number'=>$serial],PDO::FETCH_ASSOC);

        $isSpoofed = $security->locationSpoofed($d['timestamp'],$d['latitude'],$d['longitude'],$lat,$lon, threshold: 2);
        if(!$isSpoofed['isSpoofed']){
            $db->update('gps_points',[
                'latitude'=>$lat,
                'longitude'=>$lon,
                'timestamp'=>date('Y-m-d H:i:s'),
                'accuracy'=>$accuracy,
                'altitude'=>$altitude,
                'altitudeAccuracy'=>$altitudeAccuracy,
                'speed'=>$speed
            ],['serial_number'=>$serial]);
        }
    }
    $db->close();
    if(!$isSpoofed['isSpoofed']) echo json_encode(['success'=>true, 'latitude'=>$lat, 'longitude'=>$lon, 'estimateTime'=>$isSpoofed['estimateTime'], 'estimateDistance'=>$isSpoofed['distanceDifference']],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
    else echo json_encode(['success'=>false, 'latitude'=>$lat, 'longitude'=>$lon, 'msg'=>$locales->load(['errors','spoofedLocation']), 'estimateTime'=>$isSpoofed['estimateTime'], 'estimateDistance'=>$isSpoofed['distanceDifference']],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
}else{
    $results = $db->fetchAll("SELECT * FROM gps_points",[],PDO::FETCH_ASSOC);
    echo json_encode($results,JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
}



