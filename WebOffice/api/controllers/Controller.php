<?php
namespace WebOffice\api;
class Controller{
        /** 
        * __call magic method. 
        */
    public function __call($name, $arguments): never{
        $this->sendOutput('', ['HTTP/1.1 404 Not Found']);
    }
    /** 
* Get URI elements. 
* 
* @return array 
*/
    protected function getUriSegments(): array{
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = explode( '/', $uri );
        return $uri;
    }
    /** 
    * Get querystring params. 
    * 
    * @return array 
    */
    protected function getQueryStringParams(): array{
        parse_str($_SERVER['QUERY_STRING'], $query);
        return $query;
    }
    /** 
    * Send API output. 
    * 
    * @param mixed $data 
    * @param string $httpHeader 
    */
    protected function sendOutput($data, $httpHeaders=[]): never{
        header_remove('Set-Cookie');
        if (is_array($httpHeaders) && count($httpHeaders)) {
            foreach ($httpHeaders as $httpHeader) {
                header($httpHeader);
            }
        }
        echo $data;
        exit;
    }
}