<?php


namespace CupidonSauce173\PigraidNotifications\task;

use Thread;
use mysqli;
use Exception;

class MySQLThread extends Thread
{
    private string $query;
    private array $DBInfo;

    # You must prepare the whole query before sending it to the thread.
    public function __construct(string $query, array $DBInfo)
    {
        $this->query = $query;
        $this->DBInfo = $DBInfo;
    }

    public function run()
    {
        $DBInfo = $this->DBInfo;
        $db = new mysqli($DBInfo['host'], $DBInfo['username'], $DBInfo['password'], $DBInfo['database'], $DBInfo['port']);
        if($db->connect_error){
            new Exception("Couldn't connect to the MySQL database: $db->connect_error");
            return;
        }
        $db->query($this->query);
        $db->close(); # Close the connection to MySQL.
    }
}