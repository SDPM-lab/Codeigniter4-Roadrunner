<?php 
namespace SDPMlab\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use monken\Commands\CliCreate;

class initLibrary extends BaseCommand{

    protected $group       = 'ciroad';
    protected $name        = 'ciroad:init';
    protected $description = 'Init all file.';
    protected $usage = 'ciroad:init';
    protected $arguments = [];
    protected $options = [];
        
    public function run(array $params = []){

        return;
    }

}
