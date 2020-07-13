<?php
namespace SDPMlab\Ci4Roadrunner;

use Spiral\Debug;
use Laminas\Diactoros\ServerRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\HTTP\URI;
use SDPMlab\Ci4Roadrunner\Ci4UriBridge;
use SDPMlab\Ci4Roadrunner\Ci4FileBridge;

class Ci4RequestBridge 
{
    private $_rRequest;
    private $_cRequest;
    private $dumper;

    public function __construct(ServerRequest $rRequest)
    {
        $this->_rRequest = $rRequest;
        $this->dumper = new Debug\Dumper();
        $this->setFile();
        $body = $this->getBody();
        $this->_cRequest = new IncomingRequest(
            new \Config\App(),
            new URI(),
            $body,
            new UserAgent()
        );
        $this->_cRequest->uri = $this->getBridgeURI($this->_cRequest->uri);
        $this->setParams();
        $this->setHeader();
    }

    private function setFile(){
        if(count($this->_rRequest->getUploadedFiles()) > 0){
            $fileBridge = new Ci4FileBridge($this->_rRequest->getUploadedFiles());
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
        $this->_cRequest->setGlobal("get",$this->_rRequest->getQueryParams());
        if($this->_rRequest->getMethod() == "POST"){
            $this->_cRequest->setGlobal("post",$this->_rRequest->getParsedBody());
        }
        $_COOKIE = [];
        $this->_cRequest->setGlobal("cookie",$this->_rRequest->getCookieParams());
        foreach ($this->_rRequest->getCookieParams() as $key => $value) {
            $_COOKIE[$key] = $value;
        }
        if(isset($_COOKIE[config(App::class)->sessionCookieName])){
            session_id($_COOKIE[config(App::class)->sessionCookieName]);
        }
        $this->_cRequest->setGlobal("server",$this->_rRequest->getServerParams());    
    }

    private function setHeader(){
        $rHeader = $this->_rRequest->getHeaders();
        foreach ($rHeader as $key => $datas) {
            foreach ($datas as $values) {
                $this->_cRequest->setHeader($key,$values);
            }
        }
    }

    private function getBridgeURI(URI $cURI){
        $uriBridge = new Ci4UriBridge($this->_rRequest->getUri(),$cURI);
        return $uriBridge->getURI();
    }

    public function getRequest(){
        return $this->_cRequest;
    }

}
?>