<?php

namespace FFA\command;

use FFA\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class JoinCommand extends Command {
    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("ffajoin", "Únete al juego FFA", "/ffajoin");
        $this->plugin = $plugin;
        $this->setPermission("ffa.cmd.join");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "Este comando solo se puede usar en el juego!");
            return false;
        }

        $this->plugin->onJoinGameFFA($sender);

        return true;
    }
}