<?php namespace SDPMlab\Ci4Roadrunner\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class Status extends BaseCommand
{
    protected $group       = 'ciroad';
    protected $name        = 'ciroad:status';
    protected $description = 'To view the status of all active workers.';
    protected $usage = 'ciroad:status [Options]';
    protected $options = [
        '-i' => 'Interactive mode'
    ];

    public function run(array $params): void
    {       
        $isInt = CLI::getOption("i");  
        $command = $this->getCommand();
        if($isInt){
            while(true){
                exec($command,$statusArr);
                $output = "";
                foreach ($statusArr as $value) {
                    $output .= "\n".$value;
                }
                CLI::print($output);
                sleep(1);
                CLI::clearScreen();
            }
        }else{
            system($command);
        }
    }

    protected function getCommand(): string
    {
        $command = "";
        if (substr(php_uname(), 0, 7) =="Windows") {
            $command .= ROOTPATH."rr.exe http:workers -c ".ROOTPATH.".rr.yaml";
        }else{
            $command .= "cd ".ROOTPATH.";./rr http:workers";
        }
        return $command;
    }
    
}
?>
