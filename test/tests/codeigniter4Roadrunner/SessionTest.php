<?php

class SessionTest extends \CodeIgniter\Test\CIUnitTestCase
{

    public function testSession()
    {
        for ($i=0; $i < 3; $i++) { 
            //set session
            $client = \Config\Services::curlrequest([
                'base_uri' => 'http://localhost:8080/'
            ],null,null,false);
            $checkText = uniqid();
            $response = $client->post("/sessionTest/createdSession",[
                'form_params' => [
                    'text' => $checkText
                ],
                'http_errors' => false
            ]);    
            $this->assertTrue($response->getStatusCode() === 201);
            //check session
            $setCookie = $response->getHeaders()["Set-Cookie"]->getValue();
            $session = explode("=",explode(";",$setCookie)[0]);
            $response = $client->get("/sessionTest/getSessionText",[
                'headers' => [
                    'Cookie' => "{$session[0]}={$session[1]}"
                ]
            ]);
            $this->assertTrue($response->getStatusCode() === 200);
            $getServerCheckText = json_decode($response->getBody(),true)["text"];
            $this->assertTrue($getServerCheckText === $checkText);
        }
    }

}
