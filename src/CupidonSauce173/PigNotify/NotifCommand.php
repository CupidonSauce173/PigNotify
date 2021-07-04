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
    /**
     * NotifCommand constructor.
     */
    public function __construct()
    {
        parent::__construct(NotifLoader::getInstance()->config['command-main'],
            NotifLoader::getInstance()->GetText('message.command.description'),
            '/' . NotifLoader::getInstance()->config['command-main'],
            NotifLoader::getInstance()->config['command-aliases']
        );
        if (NotifLoader::getInstance()->config['use-permission']) {
            $this->setPermission('PigNotify.' . NotifLoader::getInstance()->config['permission']);
        }
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (NotifLoader::getInstance()->config['use-permission']) {
            if (!$sender->hasPermission($this->getPermission())) {
                $sender->sendMessage(NotifLoader::getInstance()->GetText('message.no.perm'));
                return;
            }
        }
        $ui = new UI();
        /** @var Player $sender */
        $ui->MainForm($sender);
    }

    public function getPlugin(): Plugin
    {
        return NotifLoader::getInstance();
    }
}