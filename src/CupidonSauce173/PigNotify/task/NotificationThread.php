<?php

declare(strict_types = 1);

namespace CupidonSauce173\PigNotify\task;


use CupidonSauce173\PigNotify\Object\Notification;
use Thread;
use mysqli;
use Exception;
use Volatile;

use function implode;
use function count;
use function array_merge;
use function str_repeat;
use function array_fill;
use function strlen;

class NotificationThread extends Thread
{
    private array $DBInfo;
    private array $idList = [];
    private string $idClause;
    private string $idTypes;

    public Volatile $container;

    /**
     * @param array $DBInfo
     * @param Volatile $container
     */
    function __construct(array $DBInfo, Volatile $container)
    {
        $this->DBInfo = $DBInfo;
        $this->container = $container;
    }

    /**
     * @throws Exception
     */
    function run(): void
    {
        $nextTime = microtime(true) + $this->container[1]['check-database-task'];

        include($this->container[0]['folder'] . '\Object\Notification.php');

        while ($this->container[3]) {
            if (microtime(true) >= $nextTime) {
                $this->ProcessThread();
                $nextTime = microtime(true) + $this->container[1]['check-database-task'];
            }
        }
    }

    /**
     * @throws Exception
     */
    function ProcessThread(): void
    {
        if (count((array)$this->container[0]['players']) === 0) return;
        # Preparing MySQLi connection.
        $db = new mysqli();
        $db->connect(
            $this->DBInfo['host'],
            $this->DBInfo['username'],
            $this->DBInfo['password'],
            $this->DBInfo['database'],
            $this->DBInfo['port']
        );
        if ($db->connect_error !== null) throw new Exception($db->connect_error);

        # Creates player param and data.
        $clause = implode(',', array_fill(0, count((array)$this->container[0]['players']), '?'));
        $types = str_repeat('s', count((array)$this->container[0]['players']));

        $i = 0;
        foreach ((array)$this->container[2] as $notifications) {
            $i = $i + count($notifications);
        }

        if ($i === 0) {
            $stmt = $db->prepare("SELECT id,displayed,player,langKey,VarKeys,event FROM notifications WHERE player IN ($clause)");
            $stmt->bind_param($types, ...(array)$this->container[0]['players']);
        } else {
            # Creates id list array.
            foreach ((array)$this->container[0]['players'] as $player) {
                if (!isset($this->container[2][$player])) return;
                foreach ($this->container[2][$player] as $notification) {
                    if ($notification instanceof Notification) {
                        if (array_search($notification->getId(), (array)$this->idList) === false) {
                            $this->idList[] = $notification->getId();
                        }
                    }
                }
            }
            # Creates id param and data.
            $this->idClause = implode(',', array_fill(0, count($this->idList), '?'));
            $idClause = $this->idClause;
            $this->idTypes = str_repeat('i', count($this->idList));

            if (empty($this->idList)) return;
            # Gets a list of already existing notification to create a smaller and more optimized query.
            $data = array_merge((array)$this->container[0]['players'], (array)$this->idList);
            $stmt = $db->prepare("SELECT id,displayed,player,langKey,VarKeys,event FROM notifications WHERE player IN ($clause) AND id NOT IN ($idClause)");
            $stmt->bind_param($types . $this->idTypes, ...$data);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result === false) return;
        $notifications = [];

        while ($row = $result->fetch_assoc()) {
            $notif = [
                'id' => $row['id'],
                'displayed' => $row['displayed'],
                'langKey' => $row['langKey'],
                'varKeys' => $row['VarKeys'],
                'event' => $row['event'],
                'player' => $row['player']
            ];
            $notifications[$row['player']][] = $notif;
            $this->idList[] = (int)$row['id'];
        }

        # Sets every existing notifications display column to 0 since they have been displayed for sure.
        if ($this->idClause !== null) {
            $idClause = $this->idClause;
            if (strlen($this->idTypes) === count((array)$this->idList)) {
                $update = $db->prepare("UPDATE notifications SET displayed = TRUE WHERE id IN ($idClause)");
                $update->bind_param($this->idTypes, ...(array)$this->idList);
                $update->execute();
            }
        }
        $db->close();
        foreach ($notifications as $notification) {
            foreach ($notification as $item) {
                # Check if player found in the container.
                if (!isset($this->container[2][$item['player']])) {
                    $this->container[2][$item['player']] = [];
                }
                $notifClass = new Notification();
                $notifClass->setId((int)$item['id']);
                $notifClass->setPlayer($item['player']);
                $notifClass->setEvent($item['event']);
                $notifClass->setLangKey($item['langKey']);
                $notifClass->setVarKeys(explode(',', $item['varKeys']));
                $notifClass->setDisplayed((bool)$item['displayed']);

                $this->container[2][$item['player']][] = $notifClass;
            }
        }
    }
}