<?php
namespace SDPMlab\Ci4Roadrunner;

class UriBridge
{
    private static $_rURI;

    public static function setUri(\Laminas\Diactoros\Uri $rURI){
        \CodeIgniter\Config\Services::uri(null,false);
        self::$_rURI = $rURI;
        self::transferPath();
        self::transferQuery();
    }

    protected static function transferPath(){
        $rPath = self::$_rURI->getPath();

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

    protected static function transferQuery(){
        \CodeIgniter\Config\Services::uri()->setQuery(self::$_rURI->getQuery());
    }

}

?>