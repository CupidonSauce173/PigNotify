<?php

declare(strict_types=1);

namespace CupidonSauce173\PigNotify\Utils;


use CupidonSauce173\PigNotify\Object\Notification;
use CupidonSauce173\PigNotify\PigNotify;
use CupidonSauce173\PigNotify\task\SQLThread;
use pocketmine\utils\TextFormat;
use function array_fill;
use function array_search;
use function count;
use function explode;
use function implode;
use function str_contains;
use function str_repeat;
use function str_replace;

class API
{
    /**
     * @param string $uuid
     * @param string $langKey
     * @param string $event
     * @param array|null $varKeys
     */
    function createNotification(string $uuid, string $langKey, string $event, array $varKeys = null): void
    {
        # Note, varKeys build = array ( 0 => "sender|server", 1 => "sender|friend1" )
        if ($varKeys !== null) {
            $keys = implode(',', $varKeys);
            $data = ['data' => [$uuid, $langKey, $keys, $event], 'types' => 'ssss'];
            $query = "INSERT INTO notifications (player,langkey,VarKeys,event) VALUES (?,?,?,?)";
        } else {
            $data = ['data' => [$uuid, $langKey, $event], 'types' => 'sss'];
            $query = "INSERT INTO notifications (player,langKey,event) VALUES (?,?,?)";
        }

        $thread = new SQLThread($query, PigNotify::getInstance()->dbInfo, $data);
        $thread->start();
    }

    /**
     * @param Notification $notification
     */
    function deleteNotification(Notification $notification): void
    {
        $id = $notification->getId();
        $key = array_search($notification, (array)PigNotify::getInstance()->container['notifications'][$notification->getPlayer()], true);
        $user = $notification->getPlayer();
        unset(PigNotify::getInstance()->container['notifications'][$user][$key]);
        $data = ['data' => $id, 'types' => 'i'];
        $thread = new SQLThread(
            "DELETE FROM notifications WHERE id = ?",
            PigNotify::getInstance()->dbInfo, $data);
        $thread->start();
    }

    /**
     * @param array $notificationList
     */
    function deleteNotifications(array $notificationList): void
    {
        $ids = [];
        /** @var Notification $notif */
        foreach ($notificationList as $notif) {
            $ids[] = $notif->getId();
            PigNotify::getInstance()->container['notifications'][$notif->getPlayer()] = [];
        }
        $types = str_repeat('i', count($ids));
        $data = ['data' => $ids, 'types' => $types];
        $clauses = implode(',', array_fill(0, count($ids), '?'));
        $thread = new SQLThread(
            "DELETE FROM notifications WHERE id IN ($clauses)",
            PigNotify::getInstance()->dbInfo, $data);
        $thread->start();
    }

    /**
     * @param Notification $notification
     * @param bool $prefix
     * @return string
     */
    function translateNotification(Notification $notification, bool $prefix = true): string
    {
        $keys = [];
        foreach ($notification->getVarKeys() as $key) {
            $values = explode('|', $key);
            $keys[$values[0]] = $values[1];
        }

        $message = $this->getText($notification->getLangKey());
        if ($message === null) {
            PigNotify::getInstance()->getLogger()->alert('langKey: ' . $notification->getLangKey() . ' is not found in the Language File. Stopping the translation.');
            return 'Error while translating';
        }
        foreach ($keys as $key => $value) {
            if (str_contains($message, '%' . $key . '%')) {
                $message = str_replace('%' . $key . '%', $value, $message);
            } else {
                $message = 'Unknown Index: ' . $key . ' with ' . $value . ' as value.';
            }
        }
        if ($prefix) return PigNotify::getInstance()->container['config']['prefix'] . TextFormat::RESET . $message;
        return $message;
    }

    /**
     * @param string $message
     * @param array|null $LangKey
     * @return string|null
     */
    function getText(string $message, array $LangKey = null): ?string
    {
        if (!isset(PigNotify::getInstance()->langKeys[$message])) return null;
        $text = PigNotify::getInstance()->langKeys[$message];
        if ($LangKey !== null) {
            $text = str_replace($LangKey[0], $LangKey[1], $text);
        }
        return $text;
    }
}