<?php

declare(strict_types=1);

namespace CupidonSauce173\PigNotify\task;


use CupidonSauce173\PigNotify\Object\Notification;
use Exception;
use mysqli;
use Thread;
use Volatile;
use function array_fill;
use function array_merge;
use function count;
use function explode;
use function implode;
use function str_repeat;
use function strlen;

class NotificationThread extends Thread
{
    public Volatile $container;
    private array $dbInfo;
    private array $idList = [];
    private string $idClause;
    private string $idTypes;

    /**
     * @param array $dbInfo
     * @param Volatile $container
     */
    function __construct(array $dbInfo, Volatile $container)
    {
        $this->dbInfo = $dbInfo;
        $this->container = $container;
    }

    /**
     * @throws Exception
     */
    function run(): void
    {
        $nextTime = microtime(true) + $this->container['config']['check-database-task'];

        include($this->container['folder'] . '\Object\Notification.php');

        while ($this->container['runThread']) {
            if (microtime(true) >= $nextTime) {
                $this->ProcessThread();
                $nextTime = microtime(true) + $this->container['config']['check-database-task'];
            }
        }
    }

    /**
     * @throws Exception
     */
    function ProcessThread(): void
    {
        if (count((array)$this->container['players']) === 0) return;
        # Preparing MySQLi connection.
        $db = new mysqli();
        $db->connect(
            $this->dbInfo['host'],
            $this->dbInfo['username'],
            $this->dbInfo['password'],
            $this->dbInfo['database'],
            $this->dbInfo['port']
        );
        if ($db->connect_error !== null) throw new Exception($db->connect_error);

        # Creates player param and data.
        $clause = implode(',', array_fill(0, count((array)$this->container['players']), '?'));
        $types = str_repeat('s', count((array)$this->container['players']));

        $i = 0;
        foreach ((array)$this->container['notifications'] as $notifications) {
            $i = $i + count($notifications);
        }

        if ($i === 0) {
            $stmt = $db->prepare("SELECT id,displayed,player,langKey,VarKeys,event FROM notifications WHERE player IN ($clause)");
            $stmt->bind_param($types, ...(array)$this->container['players']);
        } else {
            # Creates id list array.
            foreach ((array)$this->container['players'] as $player) {
                if (!isset($this->container['notifications'][$player])) return;
                foreach ($this->container['notifications'][$player] as $notification) {
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
            $data = array_merge((array)$this->container['players'], (array)$this->idList);
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
            if (strlen($this->idTypes) === count($this->idList)) {
                $update = $db->prepare("UPDATE notifications SET displayed = TRUE WHERE id IN ($idClause)");
                $update->bind_param($this->idTypes, ...(array)$this->idList);
                $update->execute();
            }
        }
        $db->close();
        foreach ($notifications as $notification) {
            foreach ($notification as $item) {
                # Check if player found in the container.
                if (!isset($this->container['notifications'][$item['player']])) {
                    $this->container['notifications'][$item['player']] = [];
                }
                $notifClass = new Notification();
                $notifClass->setId((int)$item['id']);
                $notifClass->setPlayer($item['player']);
                $notifClass->setEvent($item['event']);
                $notifClass->setLangKey($item['langKey']);
                $notifClass->setVarKeys(explode(',', $item['varKeys']));
                $notifClass->setDisplayed((bool)$item['displayed']);

                $this->container['notifications'][$item['player']][] = $notifClass;
            }
        }
    }
}