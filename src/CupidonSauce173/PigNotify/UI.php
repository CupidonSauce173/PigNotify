<?php

declare(strict_types = 1);

namespace CupidonSauce173\PigNotify;


use CupidonSauce173\PigNotify\lib\FormAPI;
use CupidonSauce173\PigNotify\Object\Notification;
use pocketmine\Player;

use function count;
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
        $notifications = NotifLoader::getInstance()->getPlayerNotifications($player->getName());
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
                    $player->sendMessage(NotifLoader::getInstance()->container[1]['prefix'] . NotifLoader::getInstance()->GetText('message.no.notif'));
                    break;
                case 1:
                    NotifLoader::getInstance()->deleteNotifications($notifications);
                    break;
                case 2:
                    break;
            }
        });
        $form->setTitle(NotifLoader::getInstance()->GetText('form.title'));
        $form->setContent(NotifLoader::getInstance()->GetText('form.content.main'));
        if ($count !== 0) {
            $form->addButton(str_replace('%count%', (string)$count, NotifLoader::getInstance()->GetText('form.notifications.button')), 0, 'textures/ui/ErrorGlyph');
        } else {
            $form->addButton(NotifLoader::getInstance()->GetText('form.notification.button'), 0, 'textures/ui/Caution');
        }
        $form->addButton(NotifLoader::getInstance()->GetText('form.clearAll.button'));
        $form->addButton(NotifLoader::getInstance()->GetText('form.close.button'));
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
        $form->setTitle(NotifLoader::getInstance()->GetText('form.title'));
        $form->setContent(NotifLoader::getInstance()->GetText('form.content.list'));
        /** @var Notification $notification */
        foreach ($notifList as $notification) {
            $form->addButton(NotifLoader::getInstance()->TranslateNotification($notification, false));
        }
        $form->addButton(NotifLoader::getInstance()->GetText('form.close.button'));
        $form->sendToPlayer($player);
    }

    /**
     * @param Player $player
     * @param Notification $notification
     */
    function SelectedNotification(Player $player, Notification $notification): void
    {
        $form = $this->api->createSimpleForm(function (Player $player) use ($notification) {
            NotifLoader::getInstance()->deleteNotification($notification);
            $this->MainForm($player);
        });
        $form->setTitle(NotifLoader::getInstance()->GetText('form.title'));
        $form->setContent(
            NotifLoader::getInstance()->TranslateNotification($notification) .
            PHP_EOL .
            NotifLoader::getInstance()->GetText('form.warn.notif')
        );
        $form->addButton(NotifLoader::getInstance()->GetText('form.close.button'));
        $form->sendToPlayer($player);
    }
}