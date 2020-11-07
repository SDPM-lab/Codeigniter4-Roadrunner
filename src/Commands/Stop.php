<?php namespace SDPMlab\Ci4Roadrunner\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class Stop extends BaseCommand
{
    protected $group       = 'ciroad';
    protected $name        = 'ciroad:stop';
    protected $description = 'Stop RoadRunner server in background mode.';
    protected $usage = 'ciroad:stop';

    public function run(array $params): void
    {           
        $command = $this->getCommand();
        system($command);
    }

    protected function getCommand(): string
    {
        $command = "";
        if (substr(php_uname(), 0, 7) =="Windows") {
            $command .= ROOTPATH."rr.exe stop -c ".ROOTPATH.".rr.yaml";
        }else{
            $command .= "cd ".ROOTPATH.";./rr stop";
        }
        return $command;
    }
    
}
?>
