<?php


namespace CupidonSauce173\PigraidNotifications\Utils;


use CupidonSauce173\PigraidNotifications\NotifLoader;
use CupidonSauce173\PigraidNotifications\Object\Notification;
use CupidonSauce173\PigraidNotifications\task\MySQLThread;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

use function array_search;
use function implode;
use function explode;
use function strpos;
use function str_replace;
use function sort;

class API
{
    public function createNotification(Player $player, string $langKey, string $event, array $varKeys = null): void
    {
        $target = $player->getName();
        $thread = new MySQLThread(
            "INSERT INTO notifications (player,langkey,VarKeys,event) VALUES ('$target','$langKey','$varKeys','$event')",
            NotifLoader::getInstance()->DBInfo
        );
        $thread->start();
    }

    public function deleteNotification(Notification $notification): void
    {
        $id = (int)$notification->getId();
        $key = array_search($notification, NotifLoader::getInstance()->notificationList[$notification->getPlayer()], true);
        $user = $notification->getPlayer();
        unset(NotifLoader::getInstance()->notificationList[$user][$key]);
        sort(NotifLoader::getInstance()->notificationList[$user]);
        $thread = new MySQLThread("DELETE FROM notifications WHERE id = $id", NotifLoader::getInstance()->DBInfo);
        $thread->start();
    }

    public function deleteNotifications(array $notificationList): void
    {
        $ids = [];
        /** @var Notification $notif */
        foreach ($notificationList as $notif) {
            $ids[] = $notif->getId();
            NotifLoader::getInstance()->notificationList[$notif->getPlayer()] = [];
        }
        $ids = implode("','", $ids);
        $thread = new MySQLThread("DELETE FROM notifications WHERE id IN ('$ids')", NotifLoader::getInstance()->DBInfo);
        $thread->start();
    }

    public function TranslateNotification(Notification $notification): string
    {
        $keys = [];
        foreach ($notification->getVarKeys() as $key) {
            $values = explode('|', $key);
            $keys[$values[0]] = $values[1];
        }

        $message = $this->GetText($notification->getLangKey());
        if ($message === false) {
            NotifLoader::getInstance()->getLogger()->alert('langKey: ' . $notification->getLangKey() . ' is not found in the Language File. Stopping the translation.');
            return 'Error while translating';
        }
        foreach ($keys as $key => $value) {
            if (strpos($message, '%' . $key . '%') !== false) {
                $message = str_replace('%' . $key . '%', $value, $message);
            } else {
                $message = 'Unknown Index: ' . $key . ' with ' . $value . ' as value.';
            }
        }
        $message = NotifLoader::getInstance()->config['prefix'] . TextFormat::RESET . $message;
        return $message;
    }

    public function GetText(string $message, array $LangKey = null): string
    {
        $text = NotifLoader::getInstance()->langKeys[$message];
        if ($LangKey !== null) {
            $text = str_replace($LangKey[0], $LangKey[1], $message);
        }
        return $text;
    }
}