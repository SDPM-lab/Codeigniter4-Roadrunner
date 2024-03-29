<?php

namespace SDPMlab\Ci4Roadrunner\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class InitLibrary extends BaseCommand
{
    protected $group       = 'ciroad';
    protected $name        = 'ciroad:init';
    protected $description = 'Initialize RoadRunner required files.';

    public function run(array $params)
    {
        CLI::write(
            CLI::color("Initializing RoadRunner Server binary ......\n", 'blue')
        );

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command = '&&vendor\\bin\\rr get';
        } else {
            $command = ';./vendor/bin/rr get';
        }

        $init = popen('cd ' . ROOTPATH . $command, 'w');
        pclose($init);

        CLI::write(
            CLI::color("\nCopy Codeigniter4 RoadRunner file ......\n", 'blue')
        );

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $word = '\\';
        } else {
            $word = '/';
        }

        copy(__DIR__ . $word . 'file' . $word . 'psr-worker.php', ROOTPATH . 'psr-worker.php');
        copy(__DIR__ . $word . 'file' . $word . '.rr.yaml', ROOTPATH . '.rr.yaml');

        CLI::write(
            CLI::color("Initialization successful!\n", 'green')
        );
    }
}
