<?php

declare(strict_types=1);

namespace CupidonSauce173\PigNotify;


use CupidonSauce173\PigNotify\Object\Notification;
use CupidonSauce173\PigNotify\task\DispatchNotifications;
use CupidonSauce173\PigNotify\task\NotificationThread;
use CupidonSauce173\PigNotify\Utils\API;
use CupidonSauce173\PigNotify\Utils\DatabaseProvider;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use Thread;
use Volatile;
use function array_map;
use function file_exists;
use function parse_ini_file;
use function preg_match;

class PigNotify extends PluginBase implements Listener
{
    static PigNotify $instance;
    public array $dbInfo;
    public array $langKeys;
    public Thread $thread;
    public Volatile $container;
    private API $api;

    # Server Events Field

    /**
     * @return PigNotify
     */
    static function getInstance(): self
    {
        return self::$instance;
    }

    function onEnable(): void
    {
        # File integrity check
        if (!file_exists($this->getDataFolder() . 'config.yml')) {
            $this->saveResource('config.yml');
        }
        if (!file_exists($this->getDataFolder() . 'langKeys.ini')) {
            $this->saveResource('langKeys.ini');
        }

        $this->iniThreadField();

        $this->langKeys = array_map('\stripcslashes', parse_ini_file($this->getDataFolder() . 'langKeys.ini', false, INI_SCANNER_RAW));
        $this->api = new API();
        $this->dbInfo = (array)$this->container['config']['MySQL'];

        if (preg_match('/[^A-Za-z-.]/', $this->container['config']['permission'])) {
            $this->getLogger()->error('Wrong permission setting. Please do not put any special characters.');
            $this->getServer()->shutdown();
        }

        new DatabaseProvider($this->dbInfo);

        $this->getScheduler()->scheduleRepeatingTask(new DispatchNotifications(), $this->container['config']['check-displayed-task'] * 20);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getCommandMap()->register('PigNotify', new Commands());
    }

    private function iniThreadField(): void
    {
        $config = new Config($this->getDataFolder() . 'config.yml', Config::YAML);

        # Prepare & Populate container.
        $this->container = new Volatile();
        $this->container['players'] = [];
        $this->container['folder'] = __DIR__;
        $this->container['config'] = $config->getAll();
        $this->container['notifications'] = []; # Contains all notification objects
        $this->container['runThread'] = true;

        # Prepare & start thread.
        $this->thread = new NotificationThread(
            (array)$this->container['config']['MySQL'],
            $this->container
        );
        $this->thread->start();
    }

    function onDisable(): void
    {
        # Stopping the NotificationThread
        $this->container['runThread'] = false;
    }

    function onLoad(): void
    {
        self::$instance = $this;
    }

    # API Field

    /**
     * Create a new notification for a player by uuid.
     * @param string $uuid
     * @param string $langKey
     * @param string $event
     * @param array|null $varKeys
     */
    function createNotification(string $uuid, string $langKey, string $event, array $varKeys = null): void
    {
        $this->api->createNotification($uuid, $langKey, $event, $varKeys);
    }

    /**
     * Get all notifications of a player by uuid.
     * @param string $uuid
     * @return array
     */
    function getPlayerNotifications(string $uuid): array
    {
        if (!isset($this->container['notifications'][$uuid])) return [];
        return (array)$this->container['notifications'][$uuid];
    }

    /**
     * Delete a specific notification. Must supply a notification object.
     * @param Notification $notification
     */
    function deleteNotification(Notification $notification): void
    {
        $this->api->deleteNotification($notification);
    }

    /**
     * Delete a list of notifications, must supply an array of notifications.
     * @param array $list
     */
    function deleteNotifications(array $list): void
    {
        $this->api->deleteNotifications($list);
    }

    /**
     * Get the text of a key from the langKeys.ini file.
     * @param string $messageKey
     * @param array|null $langKeys
     * @return string|null
     */
    function getText(string $messageKey, array $langKeys = null): ?string
    {
        return $this->api->getText($messageKey, $langKeys);
    }

    /**
     * Translate a notification to a readable message for the players.
     * @param Notification $notification
     * @param bool $prefix
     * @return string
     */
    function translateNotification(Notification $notification, bool $prefix = true): string
    {
        return $this->api->translateNotification($notification, $prefix);
    }

    # Events Field

    /**
     * @param PlayerJoinEvent $event
     */
    function onJoin(PlayerJoinEvent $event): void
    {
        $this->container['players'][] = $event->getPlayer()->getUniqueId()->toString();

        $this->createNotification(
            $event->getPlayer()->getUniqueId()->toString(),
            'friend.request.received',
            'RequestReceived',
            ['sender|TestFriend']
        );

    }

    /**
     * @param PlayerQuitEvent $event
     */
    function onLeave(PlayerQuitEvent $event): void
    {
        $uuid = $event->getPlayer()->getUniqueId()->toString();
        unset($this->container['players'][$uuid]);
        if (!isset($this->container['notifications'][$uuid])) return;
        unset($this->container['notifications'][$uuid]);
    }
}