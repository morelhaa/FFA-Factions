<?php

namespace FFA\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use FFA\Main;

class SetTitleCommand extends Command {
    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("ffasettitle", "Establece el título del servidor FFA", "/ffasettitle <título>");
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

        if (count($args) < 1) {
            $sender->sendMessage(TextFormat::RED . "Uso: /ffasettitle <título>");
            return false;
        }

        $title = implode(" ", $args);
        $this->plugin->getConfig()->set("nameserver", $title);
        $this->plugin->getConfig()->save();
        $sender->sendMessage(TextFormat::GREEN . "¡Título establecido a:  '$title'!");
        return true;
    }
}