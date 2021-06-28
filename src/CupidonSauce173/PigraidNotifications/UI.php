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
        $count = count($notifications);

        $form = $this->api->createSimpleForm(function (Player $player, $data) use ($notifications, $count) {
            if ($data === null) return;
            switch ($data) {
                case 0:
                    if($count !== 0){
                        $this->NotificationList($player, $notifications);
                     break;
                    }
                    $player->sendMessage(NotifLoader::getInstance()->config['prefix'] . NotifLoader::getInstance()->GetText('message.no.notif'));
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
            $form->addButton(str_replace('%count%' , $count, NotifLoader::getInstance()->GetText('form.notifications.button')), 0, 'textures/ui/ErrorGlyph');
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
        $form->setTitle(NotifLoader::getInstance()->GetText('form.title'));
        $form->setContent(NotifLoader::getInstance()->GetText('form.content.list'));
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
        $form->setTitle(NotifLoader::getInstance()->GetText('form.title'));
        $form->setContent(
            NotifLoader::getInstance()->TranslateNotification($notification) .
            PHP_EOL .
            NotifLoader::getInstance()->GetText('form.warn.notif')
        );
        $form->addButton('§lClose');
        $form->sendToPlayer($player);
    }
}