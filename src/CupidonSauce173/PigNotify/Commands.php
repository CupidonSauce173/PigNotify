<?php

declare(strict_types=1);

namespace CupidonSauce173\PigNotify;


use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;

class Commands extends Command implements PluginIdentifiableCommand
{

    function __construct()
    {
        parent::__construct(PigNotify::getInstance()->container[1]['command-main'],
            PigNotify::getInstance()->getText('message.command.description'),
            '/' . PigNotify::getInstance()->container[1]['command-main'],
            (array)PigNotify::getInstance()->container[1]['command-aliases']
        );
        if (PigNotify::getInstance()->container[1]['use-permission']) {
            $this->setPermission('PigNotify.' . PigNotify::getInstance()->container[1]['permission']);
        }
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (PigNotify::getInstance()->container[1]['use-permission']) {
            if (!$sender->hasPermission($this->getPermission())) {
                $sender->sendMessage(PigNotify::getInstance()->getText('message.no.perm'));
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
        return PigNotify::getInstance();
    }
}