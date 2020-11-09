<?php namespace SDPMlab\Ci4Roadrunner\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class Reset extends BaseCommand
{
    protected $group       = 'ciroad';
    protected $name        = 'ciroad:reset';
    protected $description = 'Force RoadRunner service to reload its HTTP workers.';
    protected $usage = 'ciroad:reset';

    public function run(array $params): void
    {           
        $command = $this->getCommand();
        system($command);
    }

    protected function getCommand(): string
    {
        $command = "";
        if (substr(php_uname(), 0, 7) =="Windows") {
            $command .= ROOTPATH."rr.exe http:reset -c ".ROOTPATH.".rr.yaml";
        }else{
            $command .= "cd ".ROOTPATH.";./rr http:reset";
        }
        return $command;
    }
    
}
?>
