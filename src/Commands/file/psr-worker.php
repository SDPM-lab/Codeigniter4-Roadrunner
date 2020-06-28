<?php
/**
 * @var Goridge\RelayInterface $relay
 */

ini_set('display_errors', 'stderr');
require 'vendor/autoload.php';

use Spiral\Debug;
use Spiral\Goridge;
use Spiral\RoadRunner;
use SDPMlab\Ci4Roadrunner\Ci4ResponseBridge;
use SDPMlab\Ci4Roadrunner\Ci4RequestBridge;
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

while ($req = $psr7->acceptRequest()) {
    try {
        $requestBridge = new Ci4RequestBridge($req,$dumper);
        $ci4Req = $requestBridge->getRequest();
        $app->setRequest($ci4Req);
        //$dumper->dump($requestBridge, Debug\Dumper::ERROR_LOG);
        $ci4Response = $app->run();
        $response = new Ci4ResponseBridge($ci4Response);
        //傳遞處理結果
        $psr7->respond($response);
        //初始化 CI4 以及 PHP 輸出輸入內容
        $input = fopen("php://input", "w");
        fwrite($input, "");
        fclose($input);
        ob_end_clean();
        CodeIgniter\Services::reset(true);
        $appConfig = config(\Config\App::class);
        $app       = new \CodeIgniter\CodeIgniter($appConfig);
        $app->initialize();
    } catch (\Throwable $e) {
        $dumper->dump((string)$e, Debug\Dumper::ERROR_LOG);
        $psr7->getWorker()->error((string)$e);
    }
}  