<?php
namespace SDPMlab\Ci4Roadrunner;

use CodeIgniter\HTTP\URI as CURI;
use Laminas\Diactoros\Uri as RURI;

class Ci4UriBridge
{
    private $_cURI;
    private $_rURI;

    public function __construct(RURI $rURI,CURI $cURI)
    {
        $this->_cURI = $cURI;
        $this->_rURI = $rURI;
        $this->transferAll();
    }

    private function transferAll(){
        $this->transferPath();
        $this->transferQuery();
    }

    private function transferPath(){
        $rPath = $this->_rURI->getPath();

        if($rPath == "/"){
            $this->_cURI->setPath($rPath);
            return;
        }

        $pathArr = explode("/",$rPath);
        if($pathArr[1] == "index.php"){
            unset($pathArr[1]);
            array_values($pathArr);
        }
        if($pathArr[count($pathArr)-1] == ""){
            unset($pathArr[count($pathArr)-1]);
            array_values($pathArr);
        }
        $path = "/".implode("/",$pathArr);
        $this->_cURI->setPath($path);
    }

    private function transferQuery(){
        $this->_cURI->setQuery($this->_rURI->getQuery());
    }

    public function getURI(){
        return $this->_cURI;
    }

}

?>