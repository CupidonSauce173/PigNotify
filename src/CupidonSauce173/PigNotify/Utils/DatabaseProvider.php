<?php


namespace CupidonSauce173\PigNotify\Utils;

use CupidonSauce173\PigNotify\NotifLoader;
use mysqli;

class DatabaseProvider
{
    private mysqli $db;
    private array $DBInfo;

    /**
     * DatabaseProvider constructor.
     */
    public function __construct()
    {
        $this->DBInfo = NotifLoader::getInstance()->DBInfo;
        $this->db = new mysqli($this->DBInfo['host'], $this->DBInfo['username'], $this->DBInfo['password'], $this->DBInfo['database'], $this->DBInfo['port']);
        if ($this->db->connect_error) {
            NotifLoader::getInstance()->getLogger()->error('Failed to connect to the MySQL database: ' . $this->db->connect_error);
            $this->db->close();
            NotifLoader::getInstance()->getServer()->shutdown();
        }
        $this->CreateDatabaseStructure();
    }

    # To create the database structure of the notifications.
    public function CreateDatabaseStructure(): void
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS notifications(
        id MEDIUMINT NOT NULL,
        displayed TINYINT(1) NOT NULL DEFAULT FALSE,
        player VARCHAR(15) NOT NULL,
        langKey VARCHAR(255) NOT NULL,
        VarKeys VARCHAR(255) NOT NULl,
        event VARCHAR(255) NOT NULL,
        PRIMARY KEY (id)
        )");
        $this->db->close();
    }
}