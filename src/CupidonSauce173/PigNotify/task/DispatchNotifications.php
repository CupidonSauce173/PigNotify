<?php

declare(strict_types=1);

namespace CupidonSauce173\PigNotify\task;


use CupidonSauce173\PigNotify\Object\Notification;
use CupidonSauce173\PigNotify\PigNotify;
use pocketmine\scheduler\Task;

class DispatchNotifications extends Task
{
    function onRun(): void
    {
        foreach (PigNotify::getInstance()->getServer()->getOnlinePlayers() as $player) {
            if (!isset(PigNotify::getInstance()->container['notifications'][$player->getUniqueId()->toString()])) return;
            /** @var Notification $notification */
            foreach (PigNotify::getInstance()->container['notifications'][$player->getUniqueId()->toString()] as $notification) {
                if ($notification->hasBeenDisplayed() !== true) {
                    $player->sendMessage(PigNotify::getInstance()->translateNotification($notification));
                    $notification->setDisplayed(true);
                }
            }
        }
    }
}