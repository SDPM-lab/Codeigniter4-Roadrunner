<?php
namespace SDPMlab\Ci4Roadrunner;

class HandleDBConnection
{
    public static function closeConnect()
    {
        $dbInstances = \Config\Database::getConnections();
        foreach ($dbInstances as $connection) {
            $connection->close();
        }
    }

    public static function reconnect()
    {
        $dbInstances = \Config\Database::getConnections();
        foreach ($dbInstances as $connection) {
            if($connection->DBDriver == "MySQLi"){
                try {
                    $connection->mysqli->ping();
                } catch (\Throwable $th) {
                    $connection->reconnect();
                }
            }else{
                $connection->reconnect();
            }
        }
    }
}

?>