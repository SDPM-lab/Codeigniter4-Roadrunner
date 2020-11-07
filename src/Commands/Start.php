<?php namespace SDPMlab\Ci4Roadrunner\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class Start extends BaseCommand
{
    protected $group       = 'ciroad';
    protected $name        = 'ciroad:start';
    protected $description = 'Strat Roadrunner server.';
    protected $usage = 'ciroad:start [Options]';
    protected $options = [
        '-d' => 'Run RoadRunner Server in debug mode to view all incoming requests',
        '-b' => 'Run RoadRunner Server in background.',
        '-v' => 'Verbose output'
    ];

    public function run(array $params): void
    {   
        $isDev = CLI::getOption("d");
        $isBack = CLI::getOption("b");
        $isVer = CLI::getOption("v");
        
        $command = $this->getCommand($isDev,$isVer);
        $msg = $this->getMessage($isDev,$isBack);
        
        if($isBack){
            $this->execInBackground($command);
            sleep(1);
            CLI::write($msg);
        }else{
            CLI::write($msg);
            popen($command, 'r');    
        }
    }

    protected function getCommand($isDev,$isVer): string
    {
        $command = "";
        if (substr(php_uname(), 0, 7) =="Windows") {
            $command .= ROOTPATH."rr.exe serve -c ".ROOTPATH.".rr.yaml";
        }else{
            $command .= "cd ".ROOTPATH.";./rr serve";
        }
        if($isDev) $command .= " -d";
        if($isVer) $command .= " -v";
        return $command;
    }

    protected function getMessage($isDev,$isBack): string
    {
        $msg =  CLI::color("\nCodeigniter4 Roadrunner Server Starting.\n","green");
        if($isDev) $msg .= CLI::color("Development mode.\n","yellow");
        if($isBack) $msg .= CLI::color("Bbackground mode.\n","yellow");
        return $msg;
    }

    protected function execInBackground($cmd)
    {
        if (substr(php_uname(), 0, 7) =="Windows"){
            pclose(popen("start /B ".$cmd,"r"));
        }else {
            exec($cmd ."> /dev/null &");  
        }
    }
    
}
?>
