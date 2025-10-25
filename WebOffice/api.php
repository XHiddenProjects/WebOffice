<?php
require_once "api/controllers/usersController.php";
require_once "api/controllers/ticketsController.php";
use WebOffice\api\UsersController, WebOffice\api\TicketsController;


$uri = array_values(array_filter(explode('/',parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)), fn($i)=>$i!==''));
$uri = array_splice($uri,2);


switch($uri[0]??''){
    case 'tickets':
        $objFeedController = new TicketsController();
        break;
    case 'users':
        $objFeedController = new UsersController();
    break;
    default:
        header("HTTP/1.1 404 Not Found");
        exit();
}
$strMethodName = strtolower($_SERVER['REQUEST_METHOD']);
$objFeedController->{$strMethodName}();