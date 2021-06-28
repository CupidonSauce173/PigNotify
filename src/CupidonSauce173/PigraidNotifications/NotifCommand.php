<?php


namespace CupidonSauce173\PigraidNotifications;


use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class NotifCommand extends Command
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
        $this->setPermission(NotifLoader::getInstance()->config['permission']);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (NotifLoader::getInstance()->config['permission']) {
            if (!$sender->hasPermission($this->getPermission())) {
                $sender->sendMessage(NotifLoader::getInstance()->GetText('message.no.perm'));
                return;
            }
        }
        $ui = new UI();
        /** @var Player $sender */
        $ui->MainForm($sender);
    }
}