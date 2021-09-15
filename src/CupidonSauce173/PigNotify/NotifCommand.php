<?php

declare(strict_types = 1);

namespace CupidonSauce173\PigNotify;


use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;

class NotifCommand extends Command implements PluginIdentifiableCommand
{

    function __construct()
    {
        parent::__construct(NotifLoader::getInstance()->container[1]['command-main'],
            NotifLoader::getInstance()->GetText('message.command.description'),
            '/' . NotifLoader::getInstance()->container[1]['command-main'],
            (array)NotifLoader::getInstance()->container[1]['command-aliases']
        );
        if (NotifLoader::getInstance()->container[1]['use-permission']) {
            $this->setPermission('PigNotify.' . NotifLoader::getInstance()->container[1]['permission']);
        }
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (NotifLoader::getInstance()->container[1]['use-permission']) {
            if (!$sender->hasPermission($this->getPermission())) {
                $sender->sendMessage(NotifLoader::getInstance()->GetText('message.no.perm'));
                return;
            }
        }
        $ui = new UI();
        /** @var Player $sender */
        $ui->MainForm($sender);
    }

    /**
     * @return Plugin
     */
    function getPlugin(): Plugin
    {
        return NotifLoader::getInstance();
    }
}