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

class CheckNotifications extends Thread
{
    private array $players;
    private array $DBInfo;
    private array $notifications;
    private array $idList;
    private string $idClause;
    private string $idTypes;

    public Volatile $sharedStore;

    /**
     * CheckNotifications constructor.
     * @param array $players
     * @param array $DBInfo
     * @param array $notifications
     * @param Volatile $store
     */
    public function __construct(array $players, array $DBInfo, array $notifications, Volatile $store)
    {
        $this->players = (array)$players;
        $this->DBInfo = $DBInfo;
        $this->notifications = $notifications;
        $this->sharedStore = $store;
        $this->idList = [];
    }

    /**
     * @throws Exception
     */
    public function run()
    {
        if (count($this->players) === 0) return;
        $DBInfo = $this->DBInfo;
        $db = new mysqli();
        $db->connect($DBInfo['host'], $DBInfo['username'], $DBInfo['password'], $DBInfo['database'], $DBInfo['port']);
        if ($db->connect_error !== null) throw new Exception($db->connect_error);
        # Creates player param and data.
        $clause = implode(',', array_fill(0, count($this->players), '?'));
        $types = str_repeat('s', count($this->players));
        $i = 0;
        foreach ($this->notifications as $notifications) $i = $i + count($notifications);
        if ($i === 0) {
            $stmt = $db->prepare("SELECT id,displayed,player,langKey,VarKeys,event FROM notifications WHERE player IN ($clause)");
            $stmt->bind_param($types, ...$this->players);
            $stmt->execute();
        } else {
            # Creates id list array.
            foreach ($this->players as $player) {
                /** @var Notification $notification */
                foreach ($this->notifications[$player] as $notification) {
                    $this->idList[] = $notification->getId();
                }
            }
            # Creates id param and data.
            $this->idClause = implode(',', array_fill(0, count($this->idList), '?'));
            $idClause = $this->idClause;
            $this->idTypes = str_repeat('i', count($this->idList));
            # Gets list of already existing notification ID's to create a smaller and more optimized query.
            $stmt = $db->prepare("SELECT id,displayed,player,langKey,VarKeys,event FROM notifications WHERE player IN ($clause) AND id NOT IN ($idClause)");
            $data = array_merge($this->players, (array)$this->idList);
            $stmt->bind_param($types . $this->idTypes, ...$data);
            $stmt->execute();
        }
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
            $update = $db->prepare("UPDATE notifications SET displayed = TRUE WHERE id IN ($idClause)");
            $update->bind_param($this->idTypes, ...(array)$this->idList);
            $update->execute();
        }
        $db->close();
        $this->sharedStore['notifications'] = (array)$notifications;
    }
}