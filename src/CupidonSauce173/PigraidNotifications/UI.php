<?php


namespace CupidonSauce173\PigraidNotifications;


use CupidonSauce173\PigraidNotifications\Object\Notification;
use jojoe77777\FormAPI\FormAPI;
use pocketmine\Player;

use function count;

class UI
{
    private FormAPI $api;

    public function __construct()
    {
        $this->api = NotifLoader::getInstance()->form;
    }

    /**
     * @param Player $player
     */
    public function MainForm(Player $player): void
    {
        $notifications = NotifLoader::getInstance()->getPlayerNotifications($player->getName());
        $form = $this->api->createSimpleForm(function (Player $player, $data) use ($notifications) {
            if ($data === null) return;
            switch ($data) {
                case 0:
                    $this->NotificationList($player, $notifications);
                    break;
                case 1:
                    NotifLoader::getInstance()->deleteNotifications($notifications);
                    break;
                case 2:
                    break;
            }
        });
        $form->setTitle('Notifications');
        $form->setContent("Notification system. You can see or delete all your notifications here.");
        if (count($notifications) !== 0) {
            $form->addButton('§lNotifications', 0, 'textures/ui/ErrorGlyph');
        } else {
            $form->addButton('§lNotifications', 0, 'textures/ui/Caution');
        }
        $form->addButton('§lClear all');
        $form->addButton('§lClose');
        $form->sendToPlayer($player);
    }

    /**
     * @param Player $player
     * @param array $notifList
     */
    public function NotificationList(Player $player, array $notifList): void
    {
        $form = $this->api->createSimpleForm(function (Player $player, $data) use ($notifList) {
            if ($data === null) return;
            $count = count($notifList);
            if ($data === $count) {
                return;
            }
            $this->SelectedNotification($player, $notifList[$data]);
        });
        $form->setTitle('Notifications');
        $form->setContent('Notification list.');
        /** @var Notification $notification */
        foreach ($notifList as $notification) {
            $form->addButton(NotifLoader::getInstance()->TranslateNotification($notification));
        }
        $form->addButton('§lClose');
        $form->sendToPlayer($player);
    }

    /**
     * @param Player $player
     * @param Notification $notification
     */
    public function SelectedNotification(Player $player, Notification $notification): void
    {
        $form = $this->api->createSimpleForm(function (Player $player, $data) use ($notification) {
            NotifLoader::getInstance()->deleteNotification($notification);
            $this->MainForm($player);
        });
        $form->setTitle('Notifications');
        $form->setContent(
            NotifLoader::getInstance()->TranslateNotification($notification) .
            PHP_EOL .
            '§rThis notification will be removed when you leave this page.'
        );
        $form->addButton('§lClose');
        $form->sendToPlayer($player);
    }
}