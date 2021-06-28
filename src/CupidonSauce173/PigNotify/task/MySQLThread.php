<?php


namespace CupidonSauce173\PigNotify\task;

use Thread;
use mysqli;
use Exception;
use mysqli_sql_exception;

class MySQLThread extends Thread
{
    private string $query;
    private array $DBInfo;

    # You must prepare the whole query before sending it to the thread.

    /**
     * MySQLThread constructor.
     * @param string $query
     * @param array $DBInfo
     */
    public function __construct(string $query, array $DBInfo)
    {
        $this->query = $query;
        $this->DBInfo = $DBInfo;
    }

    /**
     * @throws Exception
     */
    public function run()
    {
        $DBInfo = $this->DBInfo;
        $db = new mysqli();
        try {
            $db->connect($DBInfo['host'], $DBInfo['username'], $DBInfo['password'], $DBInfo['database'], $DBInfo['port']);
            $db->query($this->query);
            $db->close(); # Close the connection to MySQL.
        } catch (mysqli_sql_exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}