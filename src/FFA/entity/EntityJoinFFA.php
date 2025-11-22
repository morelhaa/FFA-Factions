<?php

namespace FFA\entity;

use pocketmine\entity\Human;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;

class EntityJoinFFA extends Human {

    public static function getNetworkTypeId(): string {
        return EntityIds::NPC;
    }

    protected function initEntity(CompoundTag $nbt): void {
        parent::initEntity($nbt);
        $this->setNameTagAlwaysVisible(true);
        $this->setMaxHealth(1);
        $this->setHealth(1);
    }

    public function canSaveWithChunk(): bool {
        return false;
    }

    protected function entityBaseTick(int $tickDiff = 1): bool {
        return true;
    }

    public function onUpdate(int $currentTick): bool {
        return parent::onUpdate($currentTick);
    }
    public function attack(EntityDamageEvent $source): void {
        parent::attack($source);
    }
    public function hasMovementUpdate(): bool {
        return false;
    }
    protected function applyGravity(): void {
    }
    public function onInteract(Player $player, Vector3 $clickPos): bool {
        return true;
    }
    public function isAlive(): bool {
        return true;
    }
    protected function checkBlockCollision(): void {
    }
    public function canBeMovedByCurrents(): bool {
        return false;
    }
    public function canBeCollidedWith(): bool {
        return false;
    }
}