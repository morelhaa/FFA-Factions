<?php

namespace FFA\command;

use FFA\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class AdminCommand extends Command {
    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("ffaadmin", "Comandos de administración FFA", "/ffaadmin");
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

        $sender->sendMessage(TextFormat::GOLD . " Comandos Admin FFA ");
        $sender->sendMessage(TextFormat::GREEN . "/setarena <mundo> - Establece el mundo de la arena");
        $sender->sendMessage(TextFormat::GREEN . "/npc - Crea un NPC de FFA");
        $sender->sendMessage(TextFormat::GREEN . "/removenpc - Elimina un NPC de FFA");
        $sender->sendMessage(TextFormat::GREEN . "/settitle <título> - Establece el título del servidor");
        return true;
    }
}