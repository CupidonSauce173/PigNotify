<?php

declare(strict_types=1);

namespace CupidonSauce173\PigNotify;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class Commands extends Command
{

    function __construct()
    {
        parent::__construct(PigNotify::getInstance()->container['config']['command-main'],
            PigNotify::getInstance()->getText('message.command.description'),
            '/' . PigNotify::getInstance()->container['config']['command-main'],
            (array)PigNotify::getInstance()->container['config']['command-aliases']
        );
        if (PigNotify::getInstance()->container['config']['use-permission']) {
            $this->setPermission('PigNotify.' . PigNotify::getInstance()->container['config']['permission']);
        }
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (PigNotify::getInstance()->container['config']['use-permission']) {
            if (!$sender->hasPermission($this->getPermission())) {
                $sender->sendMessage(PigNotify::getInstance()->getText('message.no.perm'));
                return;
            }
        }
        $ui = new UI();
        /** @var Player $sender */
        $ui->MainForm($sender);
    }
}