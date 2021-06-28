<?php


namespace CupidonSauce173\PigraidNotifications\task;


use CupidonSauce173\PigraidNotifications\NotifLoader;
use CupidonSauce173\PigraidNotifications\Object\Notification;
use pocketmine\scheduler\Task;

class DisplayTask extends Task
{
    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick)
    {
        foreach (NotifLoader::getInstance()->getServer()->getOnlinePlayers() as $player) {
            if (!isset(NotifLoader::getInstance()->notificationList[$player->getName()])) return;
            /** @var Notification $notification */
            foreach (NotifLoader::getInstance()->notificationList[$player->getName()] as $notification) {
                if ($notification->hasBeenDisplayed() !== true) {
                    $player = NotifLoader::getInstance()->getServer()->getPlayer($notification->getPlayer());
                    $player->sendMessage(NotifLoader::getInstance()->TranslateNotification($notification));
                    $notification->setDisplayed(true);
                }
            }
        }
    }
}