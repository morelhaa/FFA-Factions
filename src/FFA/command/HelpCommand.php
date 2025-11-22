<?php

namespace FFA\command;

use FFA\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class HelpCommand extends Command {
    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("ffahelp", "Ayuda del plugin FFA", "/ffahelp");
        $this->plugin = $plugin;
        $this->setPermission("ffa.cmd.help");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "Este comando solo se puede usar en el juego!");
            return false;
        }

        $sender->sendMessage(TextFormat::GOLD . " Ayuda FFA ");
        $sender->sendMessage(TextFormat::GREEN . "/ffajoin - Únete al FFA");
        $sender->sendMessage(TextFormat::GREEN . "/ffaquit - Sale del FFA");
        $sender->sendMessage(TextFormat::GREEN . "/ffaabout - Información del plugin");
        $sender->sendMessage(TextFormat::GREEN . "/ffahelp - Muestra esta ayuda");

        if ($sender->hasPermission("ffa.admin")) {
            $sender->sendMessage(TextFormat::YELLOW . "\nComandos de Admin:");
            $sender->sendMessage(TextFormat::GREEN . "/ffaadmin - Comandos administrativos");
        }
        return true;
    }
}