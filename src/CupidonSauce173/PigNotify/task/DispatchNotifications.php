<?php

declare(strict_types=1);

namespace CupidonSauce173\PigNotify\task;


use CupidonSauce173\PigNotify\Object\Notification;
use CupidonSauce173\PigNotify\PigNotify;
use pocketmine\scheduler\Task;

class DispatchNotifications extends Task
{
    /**
     * @param int $currentTick
     */
    function onRun(int $currentTick): void
    {
        foreach (PigNotify::getInstance()->getServer()->getOnlinePlayers() as $player) {
            if (!isset(PigNotify::getInstance()->container['notifications'][$player->getName()])) return;
            /** @var Notification $notification */
            foreach (PigNotify::getInstance()->container['notifications'][$player->getName()] as $notification) {
                if ($notification->hasBeenDisplayed() !== true) {
                    $player = PigNotify::getInstance()->getServer()->getPlayer($notification->getPlayer());
                    $player->sendMessage(PigNotify::getInstance()->translateNotification($notification));
                    $notification->setDisplayed(true);
                }
            }
        }
    }
}