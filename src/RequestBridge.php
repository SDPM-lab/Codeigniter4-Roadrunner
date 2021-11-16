<?php
namespace SDPMlab\Ci4Roadrunner;

use Psr\Http\Message\ServerRequestInterface;
use SDPMlab\Ci4Roadrunner\UriBridge;
use SDPMlab\Ci4Roadrunner\UploadedFileBridge;

class RequestBridge 
{
    private static $_rRequest;

    public static function setRequest(ServerRequestInterface $rRequest)
    {
        self::$_rRequest = $rRequest;
        self::setFile();
        $_SERVER['HTTP_USER_AGENT'] = self::$_rRequest->getHeaderLine("User-Agent");
        \CodeIgniter\Config\Services::request(new \Config\App(),false);
        \CodeIgniter\Config\Services::request()->getUserAgent()->parse($_SERVER['HTTP_USER_AGENT']);
        UriBridge::setUri(self::$_rRequest->getUri());
        \CodeIgniter\Config\Services::request()->setBody(self::getBody());
        self::setParams();
        self::setHeader();
        return \CodeIgniter\Config\Services::request();
    }

    protected static function setFile(){
        if(count(self::$_rRequest->getUploadedFiles()) > 0){
            UploadedFileBridge::getPsr7UploadedFiles(self::$_rRequest->getUploadedFiles(),true);
        }
    }

    protected static function getBody(){
        $body = "";
        if(strpos(self::$_rRequest->getHeaderLine("content-type"), "application/json") === 0){
            $body = self::$_rRequest->getBody();
        }else{
            $body = self::$_rRequest->getBody()->getContents();
        }
        return $body;
    }

    protected static function setParams(){
        \CodeIgniter\Config\Services::request()->setMethod(self::$_rRequest->getMethod());
        \CodeIgniter\Config\Services::request()->setGlobal("get",self::$_rRequest->getQueryParams());
        if(self::$_rRequest->getMethod() == "POST"){
            \CodeIgniter\Config\Services::request()->setGlobal("post",self::$_rRequest->getParsedBody());
        }
        $_COOKIE = [];
        \CodeIgniter\Config\Services::request()->setGlobal("cookie",self::$_rRequest->getCookieParams());
        foreach (self::$_rRequest->getCookieParams() as $key => $value) {
            $_COOKIE[$key] = $value;
        }
        if(isset($_COOKIE[config(App::class)->sessionCookieName])){
            session_id($_COOKIE[config(App::class)->sessionCookieName]);
        }
        \CodeIgniter\Config\Services::request()->setGlobal("server",self::$_rRequest->getServerParams());    
    }

    protected static function setHeader(){
        $rHeader = self::$_rRequest->getHeaders();
        foreach ($rHeader as $key => $datas) {
            foreach ($datas as $values) {
                \CodeIgniter\Config\Services::request()->setHeader($key,$values);
            }
        }
    }

}
?>