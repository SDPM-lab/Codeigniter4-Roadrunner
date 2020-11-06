<?php

class FileUploadTest extends \CodeIgniter\Test\CIUnitTestCase
{

    public function testFileUpload()
    {
        $client = \Config\Services::curlrequest([
            'base_uri' => 'http://localhost:8080/'
        ],null,null,false);
        $dir = dirname(__FILE__).DIRECTORY_SEPARATOR."testFiles".DIRECTORY_SEPARATOR;
        $upload1 = $dir."upload1.text";
        $upload2 = $dir."upload2.text";
        $response = $client->post("/FileUploadTest/fileUpload",[
            'multipart' => [
                "upload1" => new \CURLFile($upload1,"text/plain","upload1.text"),
                "upload2" => new \CURLFile($upload2,"text/plain","upload2.text"),
            ]
        ]);
        $this->assertTrue($response->getStatusCode() === 201);
        $getServerMD5Text = json_decode($response->getBody(),true);
        $this->assertTrue(
            $getServerMD5Text["upload1.text"] === md5_file($upload1)
            && 
            $getServerMD5Text["upload2.text"] === md5_file($upload2)
        );
    }

    public function testFileMultipleUpload()
    {
        $client = \Config\Services::curlrequest([
            'base_uri' => 'http://localhost:8080/'
        ],null,null,false);
        $dir = dirname(__FILE__).DIRECTORY_SEPARATOR."testFiles".DIRECTORY_SEPARATOR;
        $upload1 = $dir."upload1.text";
        $upload2 = $dir."upload2.text";
        $response = $client->post("/FileUploadTest/fileMultipleUpload",[
            'multipart' => [
                "data[0]" => new \CURLFile($upload1,"text/plain","upload1.text"),
                "data[1]" => new \CURLFile($upload2,"text/plain","upload2.text"),
            ]
        ]);
        $this->assertTrue($response->getStatusCode() === 201);
        $getServerMD5Text = json_decode($response->getBody(),true);
        $this->assertTrue(
            $getServerMD5Text["upload1.text"] === md5_file($upload1)
            && 
            $getServerMD5Text["upload2.text"] === md5_file($upload2)
        );
    }

}
