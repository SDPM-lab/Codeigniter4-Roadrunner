<?php
/**
 * @var Goridge\RelayInterface $relay
 */

ini_set('display_errors', 'stderr');
require 'vendor/autoload.php';

use Spiral\Goridge;
use Spiral\RoadRunner;
use SDPMlab\Ci4Roadrunner\ResponseBridge;
use SDPMlab\Ci4Roadrunner\RequestBridge;
use SDPMlab\Ci4Roadrunner\Debug\Exceptions;
use SDPMlab\Ci4Roadrunner\Debug\Toolbar;
use SDPMlab\Ci4Roadrunner\Debug\Dumper;
use SDPMlab\Ci4Roadrunner\UploadedFileBridge;
use SDPMlab\Ci4Roadrunner\HandleDBConnection;

// codeigniter4 public/index.php
$minPHPVersion = '7.2';
if (phpversion() < $minPHPVersion)
{
	die("Your PHP version must be {$minPHPVersion} or higher to run CodeIgniter. Current version: " . phpversion());
}
unset($minPHPVersion);

/**
 * Is CLI?
 *
 * Test to see if a request was made from the command line.
 *
 * @return boolean
 */
function is_cli(): bool{
    return false;
}

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
$pathsPath = FCPATH . 'app/Config/Paths.php';
chdir(__DIR__);
require $pathsPath;
$paths = new Config\Paths();
$app = require rtrim($paths->systemDirectory, '/ ') . '/bootstrap.php';

//worker setting
$worker = new RoadRunner\Worker(new Goridge\StreamRelay(STDIN, STDOUT));
$psr7 = new RoadRunner\PSR7Client($worker);

 /**
  * Dump given value into target output.
  *
  * @param mixed $value Variable
  * @param string $target Possible options: OUTPUT, RETURN, ERROR_LOG, LOGGER.
  * @return string|null
  */
function dump($value,string $target = "ERROR_LOG") : ?string{
    return Dumper::getInstance()->dump($value,$target);
}

while ($req = $psr7->acceptRequest()) {

    //handle request object
    try {
        $ci4Req = RequestBridge::setRequest($req);
    } catch (
        \Throwable $e
    ){
        dump((string)$e);
        $psr7->getWorker()->error((string)$e);
    }

    //handle debug-bar
    try{
        if(ENVIRONMENT === 'development'){
            $toolbar = new Toolbar(config('Toolbar'),$ci4Req);
            if($barResponse = $toolbar->respond()){
                $psr7->respond($barResponse);
                init();
                continue;
            }
        }
    } catch (\Throwable $e){
        dump((string)$e);
        $psr7->getWorker()->error((string)$e);
    }

    //run framework and error handling
    try{
        if(!env("CIROAD_DB_AUTOCLOSE")) HandleDBConnection::reconnect();
        $ci4Response = $app->setRequest($ci4Req)->run();
    }catch(
        \Throwable $e
    ){
        $exception = new Exceptions($req);
        $response = $exception->exceptionHandler($e);
        $psr7->respond($response);
        init();
        continue;
    }

    //handle response object
    try {
        $response = new ResponseBridge($ci4Response,$req);
        $psr7->respond($response);
        init();
    } catch (
        \Throwable $e
    ){
        dump((string)$e);
        $psr7->getWorker()->error((string)$e);
    }
}

$psr7->getWorker()->stop();

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