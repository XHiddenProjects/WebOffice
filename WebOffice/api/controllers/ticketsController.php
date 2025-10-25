<?php
namespace WebOffice\api;
use Error;
use WebOffice\api\Controller, WebOffice\api\models\TicketsModel;
use WebOffice\Config;
include_once 'Controller.php';
include_once dirname(__DIR__)."/models/ticketsModel.php";
include_once dirname(__DIR__,2)."/libs/config.lib.php";
class TicketsController extends Controller{
    private Config $config;
    public function __construct() {
        $this->config = new Config();
    }
    public function get(): never{
        $strErrorDesc = '';
        $arrQueryStringParams = $this->getQueryStringParams();
        try {
            $ticketModel = new TicketsModel($this->config->read('mysql','host'),
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
            $arrTickets = $ticketModel->getTickets($filters, $intLimit, $order);
            $responseData = json_encode($arrTickets);

        }catch (Error $e) {
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
                    $userModel = new TicketsModel($this->config->read('mysql','host'),
                $this->config->read('mysql','user'),
            $this->config->read('mysql','psw'),
        $this->config->read('mysql','db'));
            $data = [];
                foreach ($arrQueryStringParams as $key=>$value) $data[$key] = $value;
                $arrTickets = $userModel->postTickets($data);
                $responseData = json_encode($arrTickets);
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