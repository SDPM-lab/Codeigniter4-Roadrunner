<?php
namespace SDPMlab\Ci4Roadrunner;

class Ci4UriBridge
{
    private $_rURI;

    public function __construct(\Laminas\Diactoros\Uri $rURI)
    {
        \CodeIgniter\Config\Services::uri(null,false);
        $this->_rURI = $rURI;
    }

    public function setUri(){
        $this->transferPath();
        $this->transferQuery();
    }

    private function transferPath(){
        $rPath = $this->_rURI->getPath();

        if($rPath == "/"){
            \CodeIgniter\Config\Services::uri()->setPath($rPath);
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
        \CodeIgniter\Config\Services::uri()->setPath($path);

    }

    private function transferQuery(){
        \CodeIgniter\Config\Services::uri()->setQuery($this->_rURI->getQuery());
    }

}

?>