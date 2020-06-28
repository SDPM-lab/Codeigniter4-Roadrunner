<?php
namespace SDPMlab\Ci4Roadrunner;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\Response\InjectContentTypeTrait;
use Psr\Http\Message\StreamInterface;

class Ci4ResponseBridge extends Response
{
    use InjectContentTypeTrait;
    public function __construct(\CodeIgniter\HTTP\Response $ci4Response)
    {
        parent::__construct(
            $this->createBody($ci4Response),
            $this->getCi4StatusCode($ci4Response),
            $this->injectContentType(
                $this->getCi4ContentType($ci4Response),
                $this->getCi4Headers($ci4Response)
            )
        );
    }

    private function getCi4ContentType(\CodeIgniter\HTTP\Response $ci4Response) : string{
        $ci4headers = $ci4Response->getHeaders();
        return $ci4headers['Content-Type']->getValue();
    }

    private function getCi4Headers(\CodeIgniter\HTTP\Response $ci4Response) : array{
        $ci4headers = $ci4Response->getHeaders();
        $headers = [];
        foreach ($ci4headers as $key => $value){
            if($key == "Content-Type") continue ;
            $headers[$key] = $value->getValue();
        }
        return $headers;
    }

    private function getCi4StatusCode(\CodeIgniter\HTTP\Response $ci4Response) : int{
        return $ci4Response->getStatusCode();
    }

    private function createBody(\CodeIgniter\HTTP\Response $ci4Response) : StreamInterface
    {
        $html = $ci4Response->getBody();
        if ($html instanceof StreamInterface){
            return $html;
        }
        $body = new Stream('php://temp', 'wb+');
        $body->write($html);
        $body->rewind();
        return $body;
    }
}

?>