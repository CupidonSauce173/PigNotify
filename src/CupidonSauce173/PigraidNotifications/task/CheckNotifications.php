<?php


namespace CupidonSauce173\PigraidNotifications\task;

use CupidonSauce173\PigraidNotifications\Object\Notification;
use Thread;
use mysqli;
use Exception;
use Volatile;
use mysqli_sql_exception;

use function implode;
use function count;
use function mysqli_fetch_assoc;

class CheckNotifications extends Thread
{
    private array $players;
    private array $DBInfo;
    private array $notifications;

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
        $this->players = $players;
        $this->DBInfo = $DBInfo;
        $this->notifications = $notifications;
        $this->sharedStore = $store;
    }

    /**
     * @throws Exception
     */
    public function run()
    {
        if (count($this->players) === 0) return;
        $DBInfo = $this->DBInfo;
        $db = new mysqli();
        try {
            $db->connect($DBInfo['host'], $DBInfo['username'], $DBInfo['password'], $DBInfo['database'], $DBInfo['port']);
            $players = implode("','", (array)$this->players);
            if (count($this->notifications) === 0) {
                $result = $db->query("SELECT * FROM notifications WHERE player IN ('$players')");
            } else {
                $idList = [];
                foreach ($this->players as $player) {
                    /** @var Notification $notification */
                    foreach ($this->notifications[$player] as $notification) {
                        $idList[] = $notification->getId();
                    }
                }
                # Get list of already existing notification ID's to create a smaller and more optimized query.

                $idList = implode("','", $idList);
                $result = $db->query("SELECT * FROM notifications WHERE player IN ('$players') AND id NOT IN ('$idList')");
                # Set every existing notifications display column to 0 since they have been displayed for sure.
                $db->query("UPDATE notifications SET displayed = TRUE WHERE id IN ('$idList')");
            }
            if ($result === false) return;
            $notifications = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $notif = [
                    'id' => (int)$row['id'],
                    'displayed' => (bool)$row['displayed'],
                    'langKey' => $row['langKey'],
                    'varKeys' => $row['VarKeys'],
                    'event' => $row['event'],
                    'player' => $row['player']
                ];
                $notifications[$row['player']][] = $notif;
            }
            $this->sharedStore['notifications'] = (array)$notifications;
            $db->close();
        } catch (mysqli_sql_exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}