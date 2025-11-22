<?php

namespace FFA\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use FFA\Main;

class RemoveNpcCommand extends Command {
    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("ffaremovenpc", "Elimina un NPC de FFA", "/ffaremovenpc");
        $this->plugin = $plugin;
        $this->setPermission("ffa.admin");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "Este comando solo se puede usar en el juego!");
            return false;
        }

        if (!$sender->hasPermission("ffa.admin")) {
            $sender->sendMessage(TextFormat::RED . $this->plugin->notpermission);
            return false;
        }

        $this->plugin->removenpcmode[$sender->getName()] = 0;
        $sender->sendMessage(TextFormat::GREEN . "¡Golpea el NPC para eliminarlo!");
        return true;
    }
}