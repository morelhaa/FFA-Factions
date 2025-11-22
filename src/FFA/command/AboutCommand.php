<?php

namespace FFA\command;

use FFA\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class AboutCommand extends Command {
    private Main $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
        parent::__construct("ffaabout", "Información del plugin FFA", "/ffaabout");
        $this->setPermission("ffa.cmd.about");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "Este comando solo se puede usar en el juego!");
            return false;
        }

        $version = $this->plugin->getDescription()->getVersion();
        $authors = $this->plugin->getDescription()->getAuthors();
        $authorName = count($authors) > 0 ? $authors[0] : "Desconocido";

        $sender->sendMessage(TextFormat::GREEN . "\n");
        $sender->sendMessage(TextFormat::GOLD . " Información del Plugin FFA \n");
        $sender->sendMessage(TextFormat::GREEN . "Plugin: " . TextFormat::YELLOW . "CustomFFA");
        $sender->sendMessage(TextFormat::GREEN . "Versión: " . TextFormat::YELLOW . $version);
        $sender->sendMessage(TextFormat::GREEN . "Autor: " . TextFormat::YELLOW . $authorName);
        $sender->sendMessage(TextFormat::GREEN . "API: " . TextFormat::YELLOW . "5.0.0+");
        $sender->sendMessage(TextFormat::GREEN . "\n");
        return true;
    }
}