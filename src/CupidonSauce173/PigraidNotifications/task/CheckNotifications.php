<?php


namespace CupidonSauce173\PigraidNotifications\task;

use CupidonSauce173\PigraidNotifications\Object\Notification;
use Thread;
use mysqli;
use Exception;
use Volatile;

use function implode;
use function count;

class CheckNotifications extends Thread
{
    private array $players;
    private array $DBInfo;
    private array $notifications;

    public Volatile $sharedStore;

    public function __construct(array $players, array $DBInfo, array $notifications, Volatile $store)
    {
        $this->players = $players;
        $this->DBInfo = $DBInfo;
        $this->notifications = $notifications;
        $this->sharedStore = $store;
    }

    public function run()
    {
        if(count($this->players) === 0){
            return;
        }
        $DBInfo = $this->DBInfo;
        $db = new mysqli($DBInfo['host'], $DBInfo['username'], $DBInfo['password'], $DBInfo['database'], $DBInfo['port']);
        if($db->connect_error){
            new Exception("Couldn't connect to the MySQL database: $db->connect_error");
            return;
        }
        $players = implode("','", (array)$this->players);
        if(count($this->notifications) === 0){
            $result = $db->query("SELECT * FROM notifications WHERE player IN ('$players')");
        }else{
            $idList = [];
            foreach($this->players as $player){
                /** @var Notification $notification */
                foreach ($this->notifications[$player] as $notification){
                    $idList[] = $notification->getId();
                }
            }
            # Get list of already existing notification ID's to create a smaller and more optimized query.

            $idList = implode("','", $idList);
            $result = $db->query("SELECT * FROM notifications WHERE player IN ('$players') AND id NOT IN ('$idList')");
            # Set every existing notifications display column to 0 since they have been displayed for sure.
            $db->query("UPDATE notifications SET displayed = TRUE WHERE id IN ('$idList')");
        }
        if($result === false) return;
        $notifications = [];
        while($row = mysqli_fetch_assoc($result)){
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
    }
}