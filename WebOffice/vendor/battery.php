<?php
include_once dirname(__DIR__).'/init.php';
use WebOffice\Hardware;
$hardware = new Hardware();
header('Content-Type: application/json; charset=utf-8');
echo json_encode($hardware->battery(),JSON_UNESCAPED_SLASHES);