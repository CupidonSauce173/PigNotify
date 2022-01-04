<?php

declare(strict_types=1);

namespace CupidonSauce173\PigNotify\Utils;


use CupidonSauce173\PigNotify\PigNotify;
use function mysqli_connect;

class DatabaseProvider
{

    /**
     * @param array $sqlInfo
     */
    function __construct(array $sqlInfo)
    {
        $this->CreateDatabaseStructure($sqlInfo);
    }

    # To create the database structure of the notifications.
    function CreateDatabaseStructure(array $sqlInfo): void
    {
        $link = mysqli_connect(
            $sqlInfo['host'],
            $sqlInfo['username'],
            $sqlInfo['password'],
            null,
            $sqlInfo['port']
        );
        if ($link->connect_error) {
            PigNotify::getInstance()->getLogger()->error($link->connect_error);
            PigNotify::getInstance()->getServer()->shutdown();
        }
        $s_db = $link->select_db($sqlInfo['database']);
        if (!$s_db) {
            $link->query('CREATE DATABASE ' . $sqlInfo['database']);
            PigNotify::getInstance()->getLogger()->warning('PigNotify: Created database -> ' . $sqlInfo['database']);
        }
        $link->query("CREATE TABLE IF NOT EXISTS notifications(
        id MEDIUMINT NOT NULL AUTO_INCREMENT,
        displayed TINYINT(1) NOT NULL DEFAULT FALSE,
        player VARCHAR(255) NOT NULL,
        langKey VARCHAR(255) NOT NULL,
        VarKeys VARCHAR(255) NOT NULl,
        event VARCHAR(255) NOT NULL,
        PRIMARY KEY (id)
        )");
        $link->close();
    }
}