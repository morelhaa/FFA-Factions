<?php

namespace FFA\command;

use FFA\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class SetArenaCommand extends Command {
    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("ffasetarena", "Establece el mundo de la arena FFA", "/ffasetarena <nombre_mundo>");
        $this->plugin = $plugin;
        $this->setPermission("ffa.cmd.setarena");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender->hasPermission("ffa.cmd.setarena")) {
            $sender->sendMessage(TextFormat::RED . "No tienes permiso para usar este comando!");
            return false;
        }

        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "Este comando solo se puede usar en el juego!");
            return false;
        }
        if (empty($args)) {
            $worldName = $sender->getWorld()->getFolderName();
        } else {
            $worldName = $args[0];
            $world = $this->plugin->getServer()->getWorldManager()->getWorldByName($worldName);
            if ($world === null) {
                if (!$this->plugin->getServer()->getWorldManager()->loadWorld($worldName)) {
                    $sender->sendMessage(TextFormat::RED . "El mundo '{$worldName}' no existe!");
                    return false;
                }
            }
        }

        $this->plugin->setArena($worldName);

        $sender->sendMessage(TextFormat::GREEN . "¡Arena establecida correctamente!");
        $sender->sendMessage(TextFormat::YELLOW . "Mundo: " . TextFormat::WHITE . $worldName);
        $sender->sendMessage(TextFormat::GRAY . "Guardado en config.yml");

        return true;
    }
}