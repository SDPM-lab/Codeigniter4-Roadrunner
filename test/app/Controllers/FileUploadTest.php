<?php namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class FileUploadTest extends BaseController
{
    use ResponseTrait;

    protected $format = "json";

	/**
	 * form-data 
	 */
	public function fileUpload(){
        $files = $this->request->getFiles();
        $data = [];
		foreach ($files as $file) {
            $newFileName = $file->getRandomName();
            $newFilePath = WRITEPATH.'uploads'.DIRECTORY_SEPARATOR.$newFileName;
            rename($file->getTempName(),$newFilePath);
            $data[$file->getClientName()] = md5_file($newFilePath);
        }
        return $this->respondCreated($data);	
	}

	/**
	 * form-data multiple upload
	 */
	public function fileMultipleUpload(){
		$files = $this->request->getFileMultiple("data");
        $data = [];
		foreach ($files as $file) {
            $newFileName = $file->getRandomName();
            $newFilePath = WRITEPATH.'uploads'.DIRECTORY_SEPARATOR.$newFileName;
            rename($file->getTempName(),$newFilePath);
            $data[$file->getClientName()] = md5_file($newFilePath);
        }
        return $this->respondCreated($data);	
    }
    
    /**
	 * form-data multiple upload by psr-7 file interface
	 */
    public function psr7FileUpload(){
		$files = $GLOBALS["psr7Files"];
		$data = [];
		foreach ($files as  $file) {
            $fileEx = explode('.', $file->getClientFilename());
            $newFileName = uniqid(rand()).".".array_pop($fileEx);
            $newFilePath = WRITEPATH.'uploads'.DIRECTORY_SEPARATOR.$newFileName;
            $file->moveTo($newFilePath);
            $data[$file->getClientFilename()] = md5_file($newFilePath);
        }
        return $this->respondCreated($data);
	}

	/**
	 * form-data multiple upload
	 */
	public function psr7FileMultipleUpload(){
		$files = $GLOBALS["psr7Files"]["data"];
        $data = [];
		foreach ($files as  $file) {
            $fileEx = explode('.', $file->getClientFilename());
            $newFileName = uniqid(rand()).".".array_pop($fileEx);
            $newFilePath = WRITEPATH.'uploads'.DIRECTORY_SEPARATOR.$newFileName;
            $file->moveTo($newFilePath);
            $data[$file->getClientFilename()] = md5_file($newFilePath);
        }
        return $this->respondCreated($data);
	}

}
