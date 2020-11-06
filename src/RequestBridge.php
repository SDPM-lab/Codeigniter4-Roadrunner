<?php
namespace SDPMlab\Ci4Roadrunner;

use Laminas\Diactoros\ServerRequest;
use SDPMlab\Ci4Roadrunner\UriBridge;
use SDPMlab\Ci4Roadrunner\UploadedFileBridge;

class RequestBridge 
{
    private $_rRequest;

    public function __construct(ServerRequest $rRequest)
    {
        $this->_rRequest = $rRequest;
        $this->setFile();
        $_SERVER['HTTP_USER_AGENT'] = $this->_rRequest->getHeaderLine("User-Agent");
        \CodeIgniter\Config\Services::request(new \Config\App(),false);
        \CodeIgniter\Config\Services::request()->getUserAgent()->parse($_SERVER['HTTP_USER_AGENT']);
        $this->setUri();
        \CodeIgniter\Config\Services::request()->setBody($this->getBody());
        $this->setParams();
        $this->setHeader();
    }

    private function setFile(){
        if(count($this->_rRequest->getUploadedFiles()) > 0){
            UploadedFileBridge::getPsr7UploadedFiles($this->_rRequest->getUploadedFiles(),true);
        }
    }

    private function getBody(){
        $body = "";
        if(strpos($this->_rRequest->getHeaderLine("content-type"), "application/json") === 0){
            $body = $this->_rRequest->getBody();
        }else if(
            strpos($this->_rRequest->getHeaderLine("content-type"), "text/plain") === 0 ||
            strpos($this->_rRequest->getHeaderLine("content-type"), "application/javascript") === 0 ||
            strpos($this->_rRequest->getHeaderLine("content-type"), "text/html") === 0 ||
            strpos($this->_rRequest->getHeaderLine("content-type"), "application/xml") === 0
        ){
            $body = $this->_rRequest->getBody()->getContents();
        }else{
            $body = http_build_query($this->_rRequest->getParsedBody()??[]);
        }
        return $body;
    }

    private function setParams(){
        \CodeIgniter\Config\Services::request()->setMethod($this->_rRequest->getMethod());
        \CodeIgniter\Config\Services::request()->setGlobal("get",$this->_rRequest->getQueryParams());
        if($this->_rRequest->getMethod() == "POST"){
            \CodeIgniter\Config\Services::request()->setGlobal("post",$this->_rRequest->getParsedBody());
        }
        $_COOKIE = [];
        \CodeIgniter\Config\Services::request()->setGlobal("cookie",$this->_rRequest->getCookieParams());
        foreach ($this->_rRequest->getCookieParams() as $key => $value) {
            $_COOKIE[$key] = $value;
        }
        if(isset($_COOKIE[config(App::class)->sessionCookieName])){
            session_id($_COOKIE[config(App::class)->sessionCookieName]);
        }
        \CodeIgniter\Config\Services::request()->setGlobal("server",$this->_rRequest->getServerParams());    
    }

    private function setHeader(){
        $rHeader = $this->_rRequest->getHeaders();
        foreach ($rHeader as $key => $datas) {
            foreach ($datas as $values) {
                \CodeIgniter\Config\Services::request()->setHeader($key,$values);
            }
        }
    }

    private function setUri(){
        $uriBridge = new UriBridge($this->_rRequest->getUri());
        $uriBridge->setUri();
    }

    public function getRequest(){
        return \CodeIgniter\Config\Services::request();
    }

}
?>