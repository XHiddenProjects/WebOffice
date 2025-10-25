<?php
namespace WebOffice\api;
use Error;
use WebOffice\api\Controller, WebOffice\api\models\UsersModel;
use WebOffice\Config;
include_once 'Controller.php';
include_once dirname(__DIR__)."/models/usersModel.php";
include_once dirname(__DIR__,2)."/libs/config.lib.php";
class UsersController extends Controller{
    private Config $config;
    public function __construct() {
        $this->config = new Config();
    }
    public function get(): never{
        $strErrorDesc = '';
        $arrQueryStringParams = $this->getQueryStringParams();
        try {
                    $userModel = new UsersModel($this->config->read('mysql','host'),
                $this->config->read('mysql','user'),
            $this->config->read('mysql','psw'),
        $this->config->read('mysql','db'));
                $intLimit = 10;
                $order = 'ASC';
                $filters = '';

                // Set limit and order if provided
                if (isset($arrQueryStringParams['limit']) && $arrQueryStringParams['limit']) $intLimit = (int)$arrQueryStringParams['limit']; // cast to int for safety
                if (isset($arrQueryStringParams['order']) && $arrQueryStringParams['order']) $order = strtoupper($arrQueryStringParams['order']) === 'DESC' ? 'DESC' : 'ASC'; // validate input
                if(isset($arrQueryStringParams['sel']) && $arrQueryStringParams['sel']) $filters = $arrQueryStringParams['sel'];
                    $arrUsers = $userModel->getUsers($filters, $intLimit, $order);
                    $responseData = json_encode($arrUsers);
        } catch (Error $e) {
            $strErrorDesc = $e->getMessage() . ' Something went wrong! Please contact support.';
            $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
        }

        
        // send output 
        if (!$strErrorDesc) {
            $this->sendOutput(
                $responseData,
                ['Content-Type: application/json', 'HTTP/1.1 200 OK']
            );
        } else {
            $this->sendOutput(json_encode(['error' => $strErrorDesc]), 
                ['Content-Type: application/json', $strErrorHeader]
            );
        }
    }
    public function post(): never{
        $strErrorDesc = '';
        $arrQueryStringParams = $this->getQueryStringParams();
        try {
                    $userModel = new UsersModel($this->config->read('mysql','host'),
                $this->config->read('mysql','user'),
            $this->config->read('mysql','psw'),
        $this->config->read('mysql','db'));
            $data = [];
                foreach ($arrQueryStringParams as $key=>$value) $data[$key] = $value;
                $arrUsers = $userModel->postUsers($data);
                $responseData = json_encode($arrUsers);
        } catch (Error $e) {
            $strErrorDesc = $e->getMessage() . ' Something went wrong! Please contact support.';
            $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
        }
        // send output 
        if (!$strErrorDesc) {
            $this->sendOutput(
                $responseData,
                ['Content-Type: application/json', 'HTTP/1.1 200 OK']
            );
        } else {
            $this->sendOutput(json_encode(['error' => $strErrorDesc]), 
                ['Content-Type: application/json', $strErrorHeader]
            );
        }
    }
    public function put(): never{
        $strErrorDesc = '';
        $arrQueryStringParams = $this->getQueryStringParams();
        try {
                    $userModel = new UsersModel($this->config->read('mysql','host'),
                $this->config->read('mysql','user'),
            $this->config->read('mysql','psw'),
        $this->config->read('mysql','db'));
            $data = [];
            $where = [];
                foreach ($arrQueryStringParams as $key=>$value) {
                    if($key === 'username'){
                        $where[$key] = $value;
                    }else{
                        $data[$key] = $value;
                    }
                }
                if(empty($where)) throw new Error("Missing 'username' parameter for identifying the user to update.");
                $arrUsers = $userModel->putUsers($data,$where);
                $responseData = json_encode($arrUsers);
        } catch (Error $e) {
            $strErrorDesc = $e->getMessage() . ' Something went wrong! Please contact support.';
            $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
        }
        // send output 
        if (!$strErrorDesc) {
            $this->sendOutput(
                $responseData,
                ['Content-Type: application/json', 'HTTP/1.1 200 OK']
            );
        } else {
            $this->sendOutput(json_encode(['error' => $strErrorDesc]), 
                ['Content-Type: application/json', $strErrorHeader]
            );
        }
    }
    public function delete(): never{
        $strErrorDesc = '';
        $arrQueryStringParams = $this->getQueryStringParams();
        try {
                    $userModel = new UsersModel($this->config->read('mysql','host'),
                $this->config->read('mysql','user'),
            $this->config->read('mysql','psw'),
        $this->config->read('mysql','db'));
            $where = [];
                foreach ($arrQueryStringParams as $key=>$value) {
                    if($key === 'username'){
                        $where[$key] = $value;
                    }
                }
                if(empty($where)) throw new Error("Missing 'username' parameter for identifying the user to delete.");
                $arrUsers = $userModel->deleteUsers($where);
                $responseData = json_encode($arrUsers);
        } catch (Error $e) {
            $strErrorDesc = $e->getMessage() . ' Something went wrong! Please contact support.';
            $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
        }
        // send output 
        if (!$strErrorDesc) {
            $this->sendOutput(
                $responseData,
                ['Content-Type: application/json', 'HTTP/1.1 200 OK']
            );
        } else {
            $this->sendOutput(json_encode(['error' => $strErrorDesc]), 
                ['Content-Type: application/json', $strErrorHeader]
            );
        }
    }
}