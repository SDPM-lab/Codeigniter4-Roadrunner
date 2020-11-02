<?php
namespace SDPMlab\Ci4Roadrunner\Debug;

use Spiral\Debug;

class Dumper {

    private $_directives = [
        "OUTPUT"            => 0,
        "RETURN"            => 1,
        "LOGGER"            => 2,
        "ERROR_LOG"         => 3,
        "OUTPUT_CLI"        => 4,
        "OUTPUT_CLI_COLORS" => 5,
    ];
    private static $_instance = null;
    private $_dumper;

    private function __construct()
    {
        $this->_dumper = new Debug\Dumper();
        $this->_dumper->setRenderer(Debug\Dumper::ERROR_LOG, new Debug\Renderer\ConsoleRenderer());
    }

    public static function getInstance()
    {
        if (!(self::$_instance instanceof Dumper)) {
            self::$_instance = new Dumper();
        }
        return self::$_instance;
    }

    /**
     * Dump given value into target output.
     *
     * @param mixed $value
     * @param string   $target Possible options: OUTPUT, RETURN, ERROR_LOG, LOGGER.
     * @return string|null
     * @throws DumperException
     */
    public function dump($value,string $target = "ERROR_LOG") : ?string {
        return $this->_dumper->dump($value, $this->_directives[$target]);
    }

}

?>