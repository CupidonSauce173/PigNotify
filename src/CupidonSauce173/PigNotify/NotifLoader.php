<?php

declare(strict_types = 1);

namespace CupidonSauce173\PigNotify;


use CupidonSauce173\PigNotify\Object\Notification;
use CupidonSauce173\PigNotify\task\DispatchNotifications;
use CupidonSauce173\PigNotify\task\NotificationThread;
use CupidonSauce173\PigNotify\Utils\API;
use CupidonSauce173\PigNotify\Utils\DatabaseProvider;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\Config;

use Thread;
use Volatile;

use function explode;
use function file_exists;
use function array_map;
use function parse_ini_file;

class NotifLoader extends PluginBase implements Listener
{
    private API $api;

    public array $DBInfo;
    public array $langKeys;

    static NotifLoader $instance;

    public Thread $thread;
    public Volatile $container;

    function onEnable(): void
    {
        # File integrity check
        if (!file_exists($this->getDataFolder() . 'config.yml')) {
            $this->saveResource('config.yml');
        }
        if (!file_exists($this->getDataFolder() . 'langKeys.ini')) {
            $this->saveResource('langKeys.ini');
        }

        $config = new Config($this->getDataFolder() . 'config.yml', Config::YAML);

        # Preparing the volatile container
        $this->container = new Volatile();
        $this->container[0] = [];
        $this->container[0]['players'] = [];
        $this->container[0]['folder'] = __DIR__;
        $this->container[1] = $config->getAll();
        $this->container[2] = []; # Contains all notification objects
        $this->container[3] = true;

        $this->langKeys = array_map('\stripcslashes', parse_ini_file($this->getDataFolder() . 'langKeys.ini', false, INI_SCANNER_RAW));
        $this->api = new API();
        $this->DBInfo = (array)$this->container[1]['MySQL'];
        if (preg_match('/[^A-Za-z-.]/', $this->container[1]['permission'])) {
            $this->getLogger()->error('Wrong permission setting. Please do not put any special characters.');
            $this->getServer()->shutdown();
        }

        new DatabaseProvider();

        # Prepare & start thread.
        $this->thread = new NotificationThread($this->DBInfo, $this->container);
        $this->thread->start();

        $this->getScheduler()->scheduleRepeatingTask(new DispatchNotifications(), $this->container[1]['check-displayed-task'] * 20);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getCommandMap()->register('PigNotify', new NotifCommand());
    }


    function onDisable(): void
    {
        # Stopping the NotificationThread
        $this->container[3] = false;
    }

    /**
     * @return NotifLoader
     */
    static function getInstance(): self
    {
        return self::$instance;
    }

    function onLoad(): void
    {
        self::$instance = $this;
    }

    # API Section

    /**
     * @param Player $player
     * @param string $langKey
     * @param string $event
     * @param array|null $varKeys
     */
    function createNotification(Player $player, string $langKey, string $event, array $varKeys = null): void
    {
        $this->api->createNotification($player, $langKey, $event, $varKeys);
    }

    /**
     * @param string $player
     * @return array
     */
    function getPlayerNotifications(string $player): array
    {
        if (!isset($this->container[2][$player])) return [];
        return (array)$this->container[2][$player];
    }

    /**
     * @param Notification $notification
     */
    function deleteNotification(Notification $notification): void
    {
        $this->api->deleteNotification($notification);
    }

    /**
     * @param array $toDelete
     */
    function deleteNotifications(array $toDelete): void
    {
        $this->api->deleteNotifications($toDelete);
    }

    /**
     * @param string $messageKey
     * @param array|null $LangKeys
     * @return string|null
     */
    function GetText(string $messageKey, array $LangKeys = null): ?string
    {
        return $this->api->GetText($messageKey, $LangKeys);
    }

    /**
     * @param Notification $notification
     * @param bool $prefix
     * @return string
     */
    function TranslateNotification(Notification $notification, bool $prefix = true): string
    {
        return $this->api->TranslateNotification($notification, $prefix);
    }

    # Events Section

    /**
     * @param PlayerJoinEvent $event
     */
    function onJoin(PlayerJoinEvent $event): void
    {
        $this->container[0]['players'][] = $event->getPlayer()->getName();
    }

    /**
     * @param PlayerQuitEvent $event
     */
    function onLeave(PlayerQuitEvent $event): void
    {
        $name = $event->getPlayer()->getName();
        unset($this->container[0]['players'][$name]);
        if (!isset($this->container[2][$name])) return;
        unset($this->container[2][$name]);
    }
}