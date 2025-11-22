<?php

namespace FFA\command;

use FFA\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class QuitCommand extends Command {
    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("ffaquit", "Salir del FFA", "/ffaquit");
        $this->plugin = $plugin;
        $this->setPermission("ffa.cmd.quit");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "Este comando solo se puede usar en el juego!");
            return false;
        }

        if (!isset($this->plugin->players[$sender->getName()])) {
            $sender->sendMessage(TextFormat::RED . "¡No estás en la arena FFA!");
            return false;
        }

        $this->plugin->exitFFA($sender);

        return true;
    }
}