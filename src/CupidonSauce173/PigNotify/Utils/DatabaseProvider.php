<?php

declare(strict_types=1);

namespace CupidonSauce173\PigNotify\Utils;


use CupidonSauce173\PigNotify\PigNotify;
use mysqli;

class DatabaseProvider
{
    private mysqli $db;

    function __construct()
    {
        $dbInfo = PigNotify::getInstance()->dbInfo;
        $this->db = new mysqli($dbInfo['host'], $dbInfo['username'], $dbInfo['password'], $dbInfo['database'], $dbInfo['port']);
        if ($this->db->connect_error) {
            PigNotify::getInstance()->getLogger()->error('Failed to connect to the MySQL database: ' . $this->db->connect_error);
            $this->db->close();
            PigNotify::getInstance()->getServer()->shutdown();
        }
        $this->CreateDatabaseStructure();
    }

    # To create the database structure of the notifications.
    function CreateDatabaseStructure(): void
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS notifications(
        id MEDIUMINT NOT NULL AUTO_INCREMENT,
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