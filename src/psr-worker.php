<?php
/**
 * @var Goridge\RelayInterface $relay
 */

ini_set('display_errors', 'stderr');
require 'vendor/autoload.php';

use CodeIgniter\CodeIgniter;
use Spiral\Debug;
use Spiral\Goridge;
use Spiral\RoadRunner;
use SDPMlab\Ci4Roadrunner\Ci4ResponseBridge;
use SDPMlab\Ci4Roadrunner\Ci4RequestBridge;
use SDPMlab\Ci4Roadrunner\Debug\Exceptions;
// codeigniter4 public/index.php
$minPHPVersion = '7.2';
if (phpversion() < $minPHPVersion)
{
	die("Your PHP version must be {$minPHPVersion} or higher to run CodeIgniter. Current version: " . phpversion());
}
unset($minPHPVersion);
//強制使codeigniter 認為這是一般請求
function is_cli(){
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
$dumper = new Debug\Dumper();
$dumper->setRenderer(Debug\Dumper::ERROR_LOG, new Debug\Renderer\ConsoleRenderer());


$count = 0;
while ($req = $psr7->acceptRequest()) {

    //記憶體控制
    if ($count++ > 500) {
        $psr7->getWorker()->stop();
        return;
    }

    //開始邏輯
    try {
        $exception = new Exceptions($psr7,$req);
        $requestBridge = new Ci4RequestBridge($req);
        $ci4Req = $requestBridge->getRequest();
        $app->setRequest($ci4Req);
        $ci4Response = $app->run();
        $response = new Ci4ResponseBridge($ci4Response,$req);
        //傳遞處理結果
        $psr7->respond($response);
        //初始化 CI4 以及 PHP 輸出輸入內容
        init();
    } catch (
        \CodeIgniter\Router\Exceptions\RedirectException $e
    ){
        $logger = \CodeIgniter\Config\Services::logger();
        $logger->info('REDIRECTED ROUTE at ' . $e->getMessage());
        $ci4Response = service("response");
        $ci4Response->redirect(base_url($e->getMessage()), 'auto', $e->getCode());
        $ci4Response->pretend(false)->send();
        $response = new Ci4ResponseBridge($ci4Response,$req);
        $psr7->respond($response);
        init();
    } catch (
        \CodeIgniter\Exceptions\PageNotFoundException $e
    ){
        $router = \CodeIgniter\Config\Services::router();
        $ci4Response = \CodeIgniter\Config\Services::response();
        $ci4Response->setStatusCode($e->getCode());
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(ENVIRONMENT !== 'production' || is_cli() ? $e->getMessage() : '');
    } catch (
        \Throwable $e
    ){
        $dumper->dump((string)$e, Debug\Dumper::ERROR_LOG);
        $psr7->getWorker()->error((string)$e);
    }
}

function init()
{
    $input = fopen("php://input", "w");
    fwrite($input, "");
    fclose($input);
    ob_end_clean();
    \CodeIgniter\Services::reset(true);
    $appConfig = config(\Config\App::class);
    $app       = new \CodeIgniter\CodeIgniter($appConfig);
    $app->initialize();
}