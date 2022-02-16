<?php

/**
 * @internal
 */
final class BasicTest extends \CodeIgniter\Test\CIUnitTestCase
{
    public function testLoadView()
    {
        $client = \Config\Services::curlrequest([
            'base_uri' => 'http://localhost:8080/',
        ], null, null, false);
        $response = $client->get('/basicTest/loadView');
        $this->assertTrue($response->getStatusCode() === 200);
    }

    public function testEchoText()
    {
        $client = \Config\Services::curlrequest([
            'base_uri' => 'http://localhost:8080/',
        ], null, null, false);
        $response = $client->get('/basicTest/echoText');
        $this->assertTrue($response->getBody() === 'testText');
    }

    public function testUrlQuery()
    {
        $client = \Config\Services::curlrequest([
            'base_uri' => 'http://localhost:8080/',
        ], null, null, false);
        $text1    = uniqid();
        $text2    = uniqid();
        $text3    = uniqid();
        $verify   = md5($text1 . $text2 . $text3);
        $response = $client->get('/basicTest/urlqyery', [
            'query' => [
                'texts' => [$text1, $text2],
                'text3' => $text3,
            ],
        ]);
        $this->assertTrue($response->getBody() === $verify);
    }

    public function testFormParams()
    {
        $client = \Config\Services::curlrequest([
            'base_uri' => 'http://localhost:8080/',
        ], null, null, false);
        $text1    = uniqid();
        $text2    = uniqid();
        $text3    = uniqid();
        $verify   = md5($text1 . $text2 . $text3);
        $response = $client->post('/basicTest/formparams', [
            'form_params' => [
                'texts' => [$text1, $text2],
                'text3' => $text3,
            ],
        ]);
        $this->assertTrue($response->getBody() === $verify);
    }

    public function testFormParamsAndQuery()
    {
        $client = \Config\Services::curlrequest([
            'base_uri' => 'http://localhost:8080/',
        ], null, null, false);
        $text1    = uniqid();
        $text2    = uniqid();
        $verify   = md5($text1 . $text2);
        $response = $client->post('/basicTest/formparamsandquery', [
            'query' => [
                'text1' => $text1,
            ],
            'form_params' => [
                'text2' => $text2,
            ],
        ]);
        $this->assertTrue($response->getBody() === $verify);
    }

    public function testReadHeader()
    {
        for ($i = 0; $i < 2; $i++) {
            $client = \Config\Services::curlrequest([
                'base_uri' => 'http://localhost:8080/',
            ], null, null, false);
            $token    = uniqid();
            $response = $client->get('/basicTest/readHeader', [
                'headers' => [
                    'X-Auth-Token' => $token,
                ],
            ]);
            $this->assertTrue($response->getStatusCode() === 200);
            $getServerCheckText = json_decode($response->getBody(), true)['X-Auth-Token'];
            $this->assertTrue($getServerCheckText === $token);
        }
    }

    public function testSendHeader()
    {
        $tokens = [];

        for ($i = 0; $i < 2; $i++) {
            $client = \Config\Services::curlrequest([
                'base_uri' => 'http://localhost:8080/',
            ], null, null, false);
            $token    = uniqid();
            $response = $client->get('/basicTest/sendHeader');
            $this->assertTrue($response->getStatusCode() === 200);
            $tokens[] = $response->getHeader('X-Set-Auth-Token')->getValueLine();
        }
        $this->assertTrue($tokens[0] !== $token[1]);
    }
}
