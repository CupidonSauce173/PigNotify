<?php

declare(strict_types = 1);

namespace CupidonSauce173\PigNotify\task;


use Thread;
use mysqli;
use Exception;

use function str_repeat;
use function count;

class SQLThread extends Thread
{
    private string $query;
    private array $DBInfo;
    private array $data;

    # You must prepare the whole query before sending it to the thread.

    /**
     * @param string $query
     * @param array $DBInfo
     * @param ?array $data
     */
    function __construct(string $query, array $DBInfo, ?array $data)
    {
        $this->query = $query;
        $this->DBInfo = $DBInfo;
        $this->data = $data;
    }

    /**
     * @throws Exception
     */
    function run(): void
    {
        $DBInfo = $this->DBInfo;
        $db = new mysqli();
        $db->connect($DBInfo['host'], $DBInfo['username'], $DBInfo['password'], $DBInfo['database'], $DBInfo['port']);
        if ($db->connect_error !== null) throw new Exception($db->connect_error);
        $query = $db->prepare($this->query);
        if($query === false) return;
        $query->bind_param((string)$this->data['types'], ...(array)$this->data['data']);
        $query->execute();
        $db->close();
    }
}