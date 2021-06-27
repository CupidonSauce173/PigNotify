<?php


namespace CupidonSauce173\PigraidNotifications;


use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class NotifCommand extends Command
{
    public function __construct()
    {
        parent::__construct(
            'notifications',
            'Command to see all your notifications.',
            '/' . NotifLoader::getInstance()->config['command-main'],
            NotifLoader::getInstance()->config['command-aliases']
        );
        $this->setPermission(NotifLoader::getInstance()->config['permission']);
    }


    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (NotifLoader::getInstance()->config['permission']) {
            if (!$sender->hasPermission($this->getPermission())) {
                $sender->sendMessage(NotifLoader::getInstance()->config['no-permission-message']);
                return;
            }
        }
        $ui = new UI();
        /** @var Player $sender */
        $ui->MainForm($sender);
    }
}