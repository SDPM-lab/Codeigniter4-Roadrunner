<?php
namespace SDPMlab\Ci4Roadrunner;

use Laminas\Diactoros\UploadedFile;

class Ci4FileBridge 
{
    private $_rFiles;

    public function __construct(array $rFiles)
    {
        $this->_rFiles = &$rFiles;
    }

    public function setFile(){
        if(env("CIROAD_TEMP_UPLOAD")){
            $this->handleFile();
        }
        $GLOBALS["psr7Files"] = &$this->_rFiles;
    }

    private function handleFile()
    {
        foreach ($this->_rFiles as $key => $value) {
            if (is_array($value)){
                $this->multipleFile($key,$value);
                continue;
            }
            $_FILES[$key] = $this->createFile($value);
        }
    }

    private function multipleFile($field,array $arr){
        foreach ($arr as $num => $obj) {
            $_FILES[$field] = $this->createFile($obj,true,$num,$_FILES[$field]??[]);
        }
    }

    private function createFile(UploadedFile $obj,$multi = false,$key = 0,$arr = [])
    {
        $temp_file = tempnam(sys_get_temp_dir(), uniqid());
        $input = fopen($temp_file, "w");
        fwrite($input, $obj->getStream()->getContents());
        fclose($input);
        $fileArr = [];
        if($multi){
            if(count($arr) == 0){
                $fileArr = [
                    "name" => [],
                    "type" => [],
                    "tmp_name" => [],
                    "error" => [],
                    "size" => []
                ];
            }else{
                $fileArr = $arr;
            }
            $fileArr["name"][$key] = $obj->getClientFilename();
            $fileArr["type"][$key] = $obj->getClientMediaType();
            $fileArr["tmp_name"][$key] = $temp_file;
            $fileArr["error"][$key] = $obj->getError();
            $fileArr["size"][$key] = $obj->getSize();
        }else{
            $fileArr = [
                "name" => $obj->getClientFilename(),
                "type" => $obj->getClientMediaType(),
                "tmp_name" => $temp_file,
                "error" => $obj->getError(),
                "size" => $obj->getSize()
            ];
        }
        return $fileArr;
    }

}
?>