<?php
foreach(array_diff(scandir('libs'),['.','..']) as $libs){
    if(is_file(dirname(__DIR__)."/libs/$libs")) include_once dirname(__DIR__)."/libs/$libs";
}
foreach(array_diff(scandir('addons'),['.','..']) as $addons)
    include_once dirname(__DIR__)."/addons/$addons/$addons.plg.php";
include_once dirname(__DIR__).'/libs/device-detector/Spyc.php';
include_once dirname(__DIR__).'/libs/device-detector/autoload.php';
