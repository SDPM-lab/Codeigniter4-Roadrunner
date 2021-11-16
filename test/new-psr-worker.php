<?php

ini_set('display_errors', 'stderr');
include "vendor/autoload.php";

use Spiral\RoadRunner;
use Nyholm\Psr7;

use SDPMlab\Ci4Roadrunner\ResponseBridge;
use SDPMlab\Ci4Roadrunner\RequestBridge;
use SDPMlab\Ci4Roadrunner\Debug\Exceptions;
use SDPMlab\Ci4Roadrunner\Debug\Toolbar;
use SDPMlab\Ci4Roadrunner\UploadedFileBridge;
use SDPMlab\Ci4Roadrunner\HandleDBConnection;

/**
 * Is CLI?
 *
 * Test to see if a request was made from the command line.
 *
 * @return boolean
 */
function is_cli(): bool
{
    return false;
}

// CodeIgniter4 index.php loading
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
chdir(__DIR__);
$pathsConfig = FCPATH . './app/Config/Paths.php';
require realpath($pathsConfig) ?: $pathsConfig;
$paths = new Config\Paths();
$bootstrap = rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
$app       = require realpath($bootstrap) ?: $bootstrap;

$worker = RoadRunner\Worker::create();
$psrFactory = new Psr7\Factory\Psr17Factory();

$psr7 = new RoadRunner\Http\PSR7Worker($worker, $psrFactory, $psrFactory, $psrFactory);

while (true) {
    try {
        $request = $psr7->waitRequest();

        if (!($request instanceof \Psr\Http\Message\ServerRequestInterface)) {
            // Termination request received
            break;
        }

        //handle request object
        try {
            $ci4Req = RequestBridge::setRequest($request);
        } catch (\Throwable $e) {
            var_dump((string)$e);
            $psr7->getWorker()->error((string)$e);
        }

        //handle debug-bar
        try {
            if (ENVIRONMENT === 'development') {
                $toolbar = new Toolbar(config('Toolbar'), $ci4Req);
                if ($barResponse = $toolbar->respond()) {
                    $psr7->respond($barResponse);
                    init();
                    continue;
                }
            }
        } catch (\Throwable $e) {
            $psr7->getWorker()->error((string)$e);
        }

        //run framework and error handling
        try {
            if (!env("CIROAD_DB_AUTOCLOSE")) HandleDBConnection::reconnect();
            $ci4Response = $app->setRequest($ci4Req)->run();
        } catch (\Throwable $e) {

            dump($e);
            $exception = new Exceptions($request);
            $response = $exception->exceptionHandler($e);
            $psr7->respond($response);
            init();
            continue;
        }

        //handle response object
        try {
            $response = new ResponseBridge($ci4Response, $request);
            $psr7->respond($response);
            init();
        } catch (\Throwable $e) {
            var_dump((string)$e);
            $psr7->getWorker()->error((string)$e);
        }
    } catch (\Throwable $t) {
        var_dump($t);
        $psr7->respond(new Psr7\Response(400)); // Bad Request
        continue;
    }

}

function init()
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