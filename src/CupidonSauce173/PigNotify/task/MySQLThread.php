<?php


namespace CupidonSauce173\PigNotify\task;

use Thread;
use mysqli;
use Exception;

use function str_repeat;
use function count;

class MySQLThread extends Thread
{
    private string $query;
    private array $DBInfo;
    private array $data;

    # You must prepare the whole query before sending it to the thread.

    /**
     * MySQLThread constructor.
     * @param string $query
     * @param array $DBInfo
     * @param array $data
     */
    public function __construct(string $query, array $DBInfo, array $data)
    {
        $this->query = $query;
        $this->DBInfo = $DBInfo;
        $this->data = $data;
    }

    /**
     * @throws Exception
     */
    public function run()
    {
        $DBInfo = $this->DBInfo;
        $db = new mysqli();
        $db->connect($DBInfo['host'], $DBInfo['username'], $DBInfo['password'], $DBInfo['database'], $DBInfo['port']);
        if($db->connect_error !== null) throw new Exception($db->connect_error);
        $query = $db->prepare($this->query);
        $data = (array)$this->data;
        $types = str_repeat('s', count($data));
        $query->bind_param($types, ...$data);
        $query->execute();
        $db->close(); # Close the connection to MySQL.
    }
}