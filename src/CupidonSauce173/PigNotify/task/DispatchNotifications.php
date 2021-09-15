<?php

declare(strict_types = 1);

namespace CupidonSauce173\PigNotify\task;


use CupidonSauce173\PigNotify\NotifLoader;
use CupidonSauce173\PigNotify\Object\Notification;
use pocketmine\scheduler\Task;

class DispatchNotifications extends Task
{
    /**
     * @param int $currentTick
     */
    function onRun(int $currentTick): void
    {
        foreach (NotifLoader::getInstance()->getServer()->getOnlinePlayers() as $player) {
            if (!isset(NotifLoader::getInstance()->container[2][$player->getName()])) return;
            /** @var Notification $notification */
            foreach (NotifLoader::getInstance()->container[2][$player->getName()] as $notification) {
                if ($notification->hasBeenDisplayed() !== true) {
                    $player = NotifLoader::getInstance()->getServer()->getPlayer($notification->getPlayer());
                    $player->sendMessage(NotifLoader::getInstance()->TranslateNotification($notification));
                    $notification->setDisplayed(true);
                }
            }
        }
    }
}