<?php
session_start();
$response = json_decode(file_get_contents('php://input'),true);
$_SESSION['device_info'] = $response;
echo "{\"screen\":".json_encode($response,JSON_UNESCAPED_SLASHES)."}";
header('Content-Type: application/json');