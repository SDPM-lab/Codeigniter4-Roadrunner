<?php

class RestTest extends \CodeIgniter\Test\CIUnitTestCase
{

    public function testList()
    {
        $client = \Config\Services::curlrequest([
            'base_uri' => 'http://localhost:8080/'
        ],null,null,false);
        $response = $client->get("/testRest");
        $this->assertTrue($response->getStatusCode() === 200);
    }

    public function testShow()
    {
        $client = \Config\Services::curlrequest([
            'base_uri' => 'http://localhost:8080/'
        ],null,null,false);
        $id = uniqid();
        $response = $client->get("/testRest/{$id}");
        $this->assertTrue($response->getStatusCode() === 200);
        $getJson = json_decode($response->getBody(),true);
        $this->assertTrue($getJson["id"] === $id);
    }

    public function testCreate()
    {
        $client = \Config\Services::curlrequest([
            'base_uri' => 'http://localhost:8080/'
        ],null,null,false);
        $text1 = uniqid();
        $text2 = uniqid();
        $verify = md5($text1.$text2);
        $response = $client->post("/testRest",[
            'http_errors' => false,
            'json' => [
                "text1" => $text1,
                "text2" => $text2
            ]
        ]);
        $this->assertTrue($response->getStatusCode() === 201);
        $getJson = json_decode($response->getBody(),true);
        $resVerify = md5($getJson["data"]["text1"] . $getJson["data"]["text2"]);
        $this->assertTrue($resVerify === $verify);
    }

    public function testUpdate()
    {
        $client = \Config\Services::curlrequest([
            'base_uri' => 'http://localhost:8080/'
        ],null,null,false);
        $text1 = uniqid();
        $text2 = uniqid();
        $verify = md5($text1.$text2);
        $id = uniqid();
        $response = $client->put("/testRest/{$id}",[
            'http_errors' => false,
            'json' => [
                "text1" => $text1,
                "text2" => $text2
            ]
        ]);
        $this->assertTrue($response->getStatusCode() === 200);
        $getJson = json_decode($response->getBody(),true);
        $resVerify = md5($getJson["data"]["text1"] . $getJson["data"]["text2"]);
        $this->assertTrue($resVerify === $verify);
        $this->assertTrue($getJson["id"] === $id);
    }

    public function testNew()
    {
        $client = \Config\Services::curlrequest([
            'base_uri' => 'http://localhost:8080/'
        ],null,null,false);
        $response = $client->get("/testRest/new");
        $this->assertTrue($response->getStatusCode() === 200);
        $this->assertTrue($response->getBody() === "newView");
    }

    public function testEdit()
    {
        $client = \Config\Services::curlrequest([
            'base_uri' => 'http://localhost:8080/'
        ],null,null,false);
        $id = uniqid();
        $response = $client->get("/testRest/{$id}/edit");
        $this->assertTrue($response->getStatusCode() === 200);
        $this->assertTrue($response->getBody() === $id."editView");
    }

    public function testDelete()
    {
        $client = \Config\Services::curlrequest([
            'base_uri' => 'http://localhost:8080/'
        ],null,null,false);
        $id = uniqid();
        $response = $client->delete("/testRest/{$id}");
        $this->assertTrue($response->getStatusCode() === 200);
        $getJson = json_decode($response->getBody(),true);
        $this->assertTrue($getJson["id"] === $id);
    }

}
