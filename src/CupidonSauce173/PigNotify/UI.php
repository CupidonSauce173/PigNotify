<?php

declare(strict_types=1);

namespace CupidonSauce173\PigNotify;


use CupidonSauce173\PigNotify\lib\FormAPI;
use CupidonSauce173\PigNotify\Object\Notification;
use pocketmine\Player;
use function count;
use function sort;
use function str_replace;

class UI
{
    private FormAPI $api;

    function __construct()
    {
        $this->api = new FormAPI();
    }

    /**
     * @param Player $player
     */
    function MainForm(Player $player): void
    {
        $notifications = PigNotify::getInstance()->getPlayerNotifications($player->getName());
        sort($notifications);
        $count = count($notifications);

        $form = $this->api->createSimpleForm(function (Player $player, $data) use ($notifications, $count) {
            if ($data === null) return;
            switch ($data) {
                case 0:
                    if ($count !== 0) {
                        $this->NotificationList($player, $notifications);
                        break;
                    }
                    $player->sendMessage(PigNotify::getInstance()->container['config']['prefix'] . PigNotify::getInstance()->getText('message.no.notif'));
                    break;
                case 1:
                    PigNotify::getInstance()->deleteNotifications($notifications);
                    break;
                case 2:
                    break;
            }
        });
        $form->setTitle(PigNotify::getInstance()->getText('form.title'));
        $form->setContent(PigNotify::getInstance()->getText('form.content.main'));
        if ($count !== 0) {
            $form->addButton(str_replace('%count%', (string)$count, PigNotify::getInstance()->getText('form.notifications.button')), 0, 'textures/ui/ErrorGlyph');
        } else {
            $form->addButton(PigNotify::getInstance()->getText('form.notification.button'), 0, 'textures/ui/Caution');
        }
        $form->addButton(PigNotify::getInstance()->getText('form.clearAll.button'));
        $form->addButton(PigNotify::getInstance()->getText('form.close.button'));
        $form->sendToPlayer($player);
    }

    /**
     * @param Player $player
     * @param array $notifList
     */
    function NotificationList(Player $player, array $notifList): void
    {
        $form = $this->api->createSimpleForm(function (Player $player, $data) use ($notifList) {
            if ($data === null) return;
            $count = count($notifList);
            if ((int)$data === $count) return;
            $this->SelectedNotification($player, $notifList[$data]);
        });
        $form->setTitle(PigNotify::getInstance()->getText('form.title'));
        $form->setContent(PigNotify::getInstance()->getText('form.content.list'));
        /** @var Notification $notification */
        foreach ($notifList as $notification) {
            $form->addButton(PigNotify::getInstance()->translateNotification($notification, false));
        }
        $form->addButton(PigNotify::getInstance()->getText('form.close.button'));
        $form->sendToPlayer($player);
    }

    /**
     * @param Player $player
     * @param Notification $notification
     */
    function SelectedNotification(Player $player, Notification $notification): void
    {
        $form = $this->api->createSimpleForm(function (Player $player) use ($notification) {
            PigNotify::getInstance()->deleteNotification($notification);
            $this->MainForm($player);
        });
        $form->setTitle(PigNotify::getInstance()->getText('form.title'));
        $form->setContent(
            PigNotify::getInstance()->translateNotification($notification) .
            PHP_EOL .
            PigNotify::getInstance()->getText('form.warn.notif')
        );
        $form->addButton(PigNotify::getInstance()->getText('form.close.button'));
        $form->sendToPlayer($player);
    }
}