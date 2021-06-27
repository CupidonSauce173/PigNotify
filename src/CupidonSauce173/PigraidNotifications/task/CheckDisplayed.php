<?php


namespace CupidonSauce173\PigraidNotifications\task;


use CupidonSauce173\PigraidNotifications\NotifLoader;
use CupidonSauce173\PigraidNotifications\Object\Notification;
use pocketmine\scheduler\Task;

use function spl_object_id;

class CheckDisplayed extends Task
{

    public function onRun(int $currentTick)
    {
        foreach(NotifLoader::getInstance()->getServer()->getOnlinePlayers() as $player){
            if(!isset(NotifLoader::getInstance()->notificationList[$player->getName()])) return;
            /** @var Notification $notification */
            foreach (NotifLoader::getInstance()->notificationList[$player->getName()] as $notification) {
                if ($notification->hasBeenDisplayed() !== true) {
                    var_dump('CheckDisplayed Task object ID: ' . spl_object_id($notification));
                    $player = NotifLoader::getInstance()->getServer()->getPlayer($notification->getPlayer());
                    $player->sendMessage(NotifLoader::getInstance()->TranslateNotification($notification));
                    $notification->setDisplayed(true);
                }
            }
        }
    }

}