<?php namespace SDPMlab\Ci4Roadrunner\Test;

use SDPMlab\Ci4Roadrunner\RequestHandler;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\ServerRequest;
use CodeIgniter\Config\Services;

class RequestHandlerTest extends \CodeIgniter\Test\CIUnitTestCase
{

    protected function setUp(): void
	{
		parent::setUp();
        \CodeIgniter\Config\Services::reset(true);
    }
    
    public function testNegotiatesLocale()
	{
        $server=[];
        $server['HTTP_USER_AGENT'] = 'Mozilla';
        $psrRequest = ServerRequestFactory::fromGlobals($server);
        RequestHandler::initRequest($psrRequest);
        $ci4Request = Services::request();
		$this->assertEquals('Mozilla', $ci4Request->getUserAgent());
    }

    public function testNegotiatesNot()
	{
        $server=[];
        $server['HTTP_ACCEPT_CHARSET'] = 'iso-8859-5, unicode-1-1;q=0.8';
        $psrRequest = ServerRequestFactory::fromGlobals($server);
        RequestHandler::initRequest($psrRequest);
        $ci4Request = Services::request();
        $this->assertEquals(strtolower($ci4Request->config->charset), $ci4Request->negotiate('charset', ['iso-8859', 'unicode-1-2']));
	}

    public function testNegotiatesMedia()
	{
        $server=[];
        $server['HTTP_ACCEPT'] = 'text/plain; q=0.5, text/html, text/x-dvi; q=0.8, text/x-c';
        $psrRequest = ServerRequestFactory::fromGlobals($server);
        RequestHandler::initRequest($psrRequest);
        $ci4Request = Services::request();
		$this->assertEquals('text/html', $ci4Request->negotiate('media', ['text/html', 'text/x-c', 'text/x-dvi', 'text/plain']));
	}

	public function testNegotiatesEncoding()
	{
        $server=[];
        $server['HTTP_ACCEPT_ENCODING'] = 'gzip;q=1.0, identity; q=0.4, compress;q=0.5';
        $psrRequest = ServerRequestFactory::fromGlobals($server);
        RequestHandler::initRequest($psrRequest);
        $ci4Request = Services::request();
		$this->assertEquals('gzip', $ci4Request->negotiate('encoding', ['gzip', 'compress']));
    }
    
    public function testNegotiatesLanguage()
	{
        $server=[];
        $server['HTTP_ACCEPT_LANGUAGE'] = 'da;q=1.0, en-gb;q=0.8, en;q=0.7';
        $psrRequest = ServerRequestFactory::fromGlobals($server);
        RequestHandler::initRequest($psrRequest);
        $ci4Request = Services::request();
		$this->assertEquals('da', $ci4Request->negotiate('language', ['en', 'da']));
    }
    
    public function testCanGrabGetRawJSON()
	{
        $json = '{"code":1, "message":"ok"}';
        $headers=[
            "Content-Length" => strlen($json),
            'Content-Type' => "application/json; charset=utf-8"
        ];
		$expected = [
			'code'    => 1,
			'message' => 'ok',
        ];
        $psrRequest = new ServerRequest(
            [],
            [],
            "/api/create",
            "POST",
            fopen('data://text/plain,' . $json,'r'),
            $headers
        );
        RequestHandler::initRequest($psrRequest);
        $ci4Request = Services::request();
		$this->assertEquals($expected, $ci4Request->getJSON(true));
    }
    
    public function testCanGrabGetRawInput()
	{
		$rawstring = 'username=admin001&role=administrator&usepass=0';
		$expected = [
			'username' => 'admin001',
			'role'     => 'administrator',
			'usepass'  => 0,
        ];
        $psrRequest = new ServerRequest(
            [],
            [],
            "/api/put",
            "PUT",
            fopen('data://text/plain,' . $rawstring,'r')
        );
        RequestHandler::initRequest($psrRequest);
        $ci4Request = Services::request();
		$this->assertEquals($expected, $ci4Request->getRawInput());
	}

    public function testIsAJAX()
	{
        $server=[];
        $server['HTTP_X-Requested-With'] = 'XMLHttpRequest';
        $psrRequest = ServerRequestFactory::fromGlobals($server);
        RequestHandler::initRequest($psrRequest);
        $ci4Request = Services::request();
		$this->assertTrue($ci4Request->isAJAX());
    }
    
    public function testIsSecureFrontEnd()
	{
        $server=[];
        $server['HTTP_Front-End-Https'] = 'on';
        $psrRequest = ServerRequestFactory::fromGlobals($server);
        RequestHandler::initRequest($psrRequest);
        $ci4Request = Services::request();
		$this->assertTrue($ci4Request->isSecure());
	}

	public function testIsSecureForwarded()
	{
        $server=[];
        $server['HTTP_X-Forwarded-Proto'] = 'https';
        $psrRequest = ServerRequestFactory::fromGlobals($server);
        RequestHandler::initRequest($psrRequest);
        $ci4Request = Services::request();
		$this->assertTrue($ci4Request->isSecure());
	}
    
    public function testSpoofing()
	{
        $server=[];
        $server['REQUEST_METHOD'] = 'WINK';
        $psrRequest = ServerRequestFactory::fromGlobals($server);
        RequestHandler::initRequest($psrRequest);
        $ci4Request = Services::request();
		$this->assertEquals('wink', $ci4Request->getMethod());
    }

}
