<?php

namespace FFA\task;

use FFA\Main;
use FFA\entity\EntityJoinFFA;
use pocketmine\scheduler\Task;
use pocketmine\math\Vector2;

class EntityTag extends Task {

    private Main $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function onRun(): void {
        $world = $this->plugin->getServer()->getWorldManager()->getDefaultWorld();
        if ($world === null) return;

        foreach ($world->getEntities() as $entity) {
            if ($entity instanceof EntityJoinFFA) {
                $this->sendMovement($entity);
            }
        }
    }

    private function sendMovement(EntityJoinFFA $entity): void {
        $location = $entity->getLocation();

        foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
            $playerLoc = $player->getLocation();
            $entityVec = new Vector2($location->x, $location->z);
            $playerVec = new Vector2($playerLoc->x, $playerLoc->z);

            $distance = $entityVec->distance($playerVec);

            if ($distance <= 10) {
                $entity->setNameTag("§l§aToca para Jugar\n§r§eClick to Join FFA\n§7" . $player->getName());
            } else {
                $entity->setNameTag("§l§aToca para Jugar\n§r§eClick to Join FFA");
            }
        }
    }
}

