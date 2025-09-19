<?php
require_once "api/controllers/usersController.php";
use WebOffice\api\UsersController;


$uri = array_values(array_filter(explode('/',parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)), fn($i)=>$i!==''));
$uri = array_splice($uri,2);
if ((isset($uri[0]) && $uri[0] !== 'users')) {
    header("HTTP/1.1 404 Not Found");
    exit();
}

$objFeedController = new UsersController();
$strMethodName = strtolower($_SERVER['REQUEST_METHOD']);
$objFeedController->{$strMethodName}();