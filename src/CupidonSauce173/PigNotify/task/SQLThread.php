<?php

declare(strict_types=1);

namespace CupidonSauce173\PigNotify\task;


use Exception;
use mysqli;
use Thread;

class SQLThread extends Thread
{
    private string $query;
    private array $dbInfo;
    private array $data;

    # You must prepare the whole query before sending it to the thread.

    /**
     * @param string $query
     * @param array $dbInfo
     * @param ?array $data
     */
    function __construct(string $query, array $dbInfo, ?array $data)
    {
        $this->query = $query;
        $this->dbInfo = $dbInfo;
        $this->data = $data;
    }

    /**
     * @throws Exception
     */
    function run(): void
    {
        $dbInfo = $this->dbInfo;
        $db = new mysqli();
        $db->connect($dbInfo['host'], $dbInfo['username'], $dbInfo['password'], $dbInfo['database'], $dbInfo['port']);
        if ($db->connect_error !== null) throw new Exception($db->connect_error);
        $query = $db->prepare($this->query);
        if ($query === false) return;
        $query->bind_param((string)$this->data['types'], ...(array)$this->data['data']);
        $query->execute();
        $db->close();
    }
}