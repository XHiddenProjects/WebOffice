<?php
session_start();
$response = json_decode(file_get_contents('php://input'),true);
$_SESSION['device_info'] = $response;
echo '{"status":true}';
header('Content-Type: application/json');