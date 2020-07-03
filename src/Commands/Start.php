<?php namespace SDPMlab\Ci4Roadrunner\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class Start extends BaseCommand
{
    protected $group       = 'ciroad';
    protected $name        = 'ciroad:start';
    protected $description = 'Strat Roadrunner server.';

    public function run(array $params)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command = "&&rr.exe serve -v -d";
        }else{
            $command = ";./rr serve -v -d";
        }
        CLI::write(
            CLI::color("\nCodeigniter4 Roadrunner Server Starting.\n","green")
        );
        popen("cd ".ROOTPATH.$command, 'r');
    }
}
?>
