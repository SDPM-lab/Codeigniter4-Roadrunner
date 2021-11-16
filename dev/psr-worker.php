<?php

include "vendor/autoload.php";

use CodeIgniter\CodeIgniter;
use Spiral\RoadRunner;
use Nyholm\Psr7;
use SDPMlab\Ci4Roadrunner\ResponseBridge;
use SDPMlab\Ci4Roadrunner\RequestBridge;
use SDPMlab\Ci4Roadrunner\Debug\Exceptions;
use SDPMlab\Ci4Roadrunner\Debug\Toolbar;

use SDPMlab\Ci4Roadrunner\RequestHandler;
use SDPMlab\Ci4Roadrunner\UploadedFileBridge;
use SDPMlab\Ci4Roadrunner\HandleDBConnection;

// CodeIgniter4 init
function is_cli(): bool
{
    return false;
}
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
chdir(__DIR__);
$pathsConfig = FCPATH . './app/Config/Paths.php';
require realpath($pathsConfig) ?: $pathsConfig;
$paths = new Config\Paths();
$bootstrap = rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
$app       = require realpath($bootstrap) ?: $bootstrap;

//roadrunner worker init
$worker = RoadRunner\Worker::create();
$psrFactory = new Psr7\Factory\Psr17Factory();
$psr7 = new RoadRunner\Http\PSR7Worker($worker, $psrFactory, $psrFactory, $psrFactory);

while (true) {
    try {
        $request = $psr7->waitRequest();
        if (!($request instanceof \Psr\Http\Message\ServerRequestInterface)) { // Termination request received
            break;
        }
    } catch (\Exception $e) {
        $psr7->respond(new Psr7\Response(400)); // Bad Request
        continue;
    }

    //handle request object
    try {
        $ci4Request = RequestHandler::initRequest($request);
        // $ci4Req = RequestBridge::setRequest($request);
    } catch (\Throwable $e) {
        var_dump((string)$e);
        $psr7->getWorker()->error((string)$e);
    }
    //run framework and error handling
    $app->setRequest($ci4Request)->run();
    $ci4Response = \CodeIgniter\Config\Services::response();
    
    // dump(\CodeIgniter\Config\Services::response());
    // var_dump(\CodeIgniter\Config\Services::response());
    // try {
    //     if (!env("CIROAD_DB_AUTOCLOSE")) HandleDBConnection::reconnect();
    //     $ci4Response = $app->run();
    // } catch (\Throwable $e) {
    //     dump($e);
    //     $exception = new Exceptions($request);
    //     $response = $exception->exceptionHandler($e);
    //     $psr7->respond($response);
    //     refreshCodeIgniter4();
    //     continue;
    // }

    try {
        // Application code logic
        $psr7->respond(new Psr7\Response(200, [], $ci4Response->getBody()));
        refreshCodeIgniter4();
    } catch (\Exception $e) {
        $psr7->respond(new Psr7\Response(500, [], 'Something Went Wrong!'));
    }
}

function refreshCodeIgniter4()
{
    $input = fopen("php://input", "w");
    fwrite($input, "");
    fclose($input);
    try {
        ob_end_clean();
    } catch (\Throwable $th) {}

    \CodeIgniter\Config\Services::reset(true);
    
    UploadedFileBridge::reset();

    if(env("CIROAD_DB_AUTOCLOSE")){
        HandleDBConnection::closeConnect();
    }

    $appConfig = config(\Config\App::class);
    $app       = new \CodeIgniter\CodeIgniter($appConfig);
    $app->initialize();
}