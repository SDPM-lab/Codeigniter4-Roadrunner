<?php
namespace SDPMlab\Ci4Roadrunner;

use Laminas\Diactoros\Uri as RURI;
use Spiral\Debug;


class Ci4UriBridge
{
    private $_rURI;
    private $dumper;

    public function __construct(RURI $rURI)
    {
        \CodeIgniter\Config\Services::uri(null,false);
        $this->_rURI = $rURI;
        $this->dumper = new Debug\Dumper();
        $this->transferAll();

    }

    private function transferAll(){
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

    public function getURI(){
        return \CodeIgniter\Config\Services::uri();
    }

}

?>