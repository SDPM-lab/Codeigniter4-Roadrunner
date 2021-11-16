<?php

include "vendor/autoload.php";

use CodeIgniter\CodeIgniter;
use Spiral\RoadRunner;
use Nyholm\Psr7;
use SDPMlab\Ci4Roadrunner\ResponseBridge;
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
    //get psr7 request
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
    } catch (\Throwable $e) {
        var_dump((string)$e);
        $psr7->getWorker()->error((string)$e);
    }

    //handle debug-bar
    try {
        if (ENVIRONMENT === 'development') {
            \Kint\Kint::$mode_default_cli = null;
            $toolbar = new Toolbar(config('Toolbar'), $ci4Request);
            if ($barResponse = $toolbar->respond()) {
                $psr7->respond($barResponse);
                refreshCodeIgniter4();
                unset($app);
                continue;
            }
        }
    } catch (\Throwable $e) {
        $psr7->getWorker()->error((string)$e);
    }
    
    //run framework and error handling
    try {
        if (!env("CIROAD_DB_AUTOCLOSE")) HandleDBConnection::reconnect();
        $appConfig = config(\Config\App::class);
        $app       = new \CodeIgniter\CodeIgniter($appConfig);
        $app->initialize();
        $app->setRequest($ci4Request)->run();
        $ci4Response = \CodeIgniter\Config\Services::response();
    } catch (\Throwable $e) {
        $exception = new Exceptions($request);
        $response = $exception->exceptionHandler($e);
        $psr7->respond($response);
        refreshCodeIgniter4();
        unset($app);
        continue;
    }

    //handle response object
    try {
        // Application code logic
        $response = new ResponseBridge($ci4Response, $request);
        $psr7->respond($response);
        refreshCodeIgniter4();
        unset($app);
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
}