<?php

namespace FFA;

use FFA\command\AboutCommand;
use FFA\command\AdminCommand;
use FFA\command\HelpCommand;
use FFA\command\JoinCommand;
use FFA\command\NpcCommand;
use FFA\command\QuitCommand;
use FFA\command\RemoveNpcCommand;
use FFA\command\SetArenaCommand;
use FFA\command\SetTitleCommand;
use FFA\entity\EntityJoinFFA;
use FFA\task\EntityTag;
use FFA\task\ScoreboardUpdateTask;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\VanillaItems;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;
use pocketmine\nbt\tag\CompoundTag;

class Main extends PluginBase implements Listener {

    public array $removenpcmode = [];
    public array $players = [];

    private array $savedInventories = [];

    private array $savedArmor = [];

    private array $savedGameMode = [];

    private array $playerStats = [];

    private ?ScoreboardManager $scoreboardManager = null;

    public function getScoreboardManager(): ?ScoreboardManager {
        return $this->scoreboardManager;
    }

    public function onEnable(): void {

        EntityFactory::getInstance()->register(EntityJoinFFA::class, function(World $world, CompoundTag $nbt) : Entity {
            return new EntityJoinFFA($world, $nbt);
        }, ["FFA:JoinEntity"]);

        $this->scoreboardManager = new ScoreboardManager($this);

        $this->getScheduler()->scheduleRepeatingTask(new EntityTag($this), 20);
        $this->getScheduler()->scheduleRepeatingTask(new ScoreboardUpdateTask($this), 20);

        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        $this->registerCommands();
        $this->loadStats();
        $this->loadNPCs();

        $this->getLogger()->info("§aFFA Enabled");
        $this->getLogger()->info("§aSupport Discord: dmin1s._");
    }

    private function registerCommands(): void {
        $map = $this->getServer()->getCommandMap();
        $map->register("ffa", new JoinCommand($this));
        $map->register("ffa", new QuitCommand($this));
        $map->register("ffa", new NpcCommand($this));
        $map->register("ffa", new RemoveNpcCommand($this));
        $map->register("ffa", new SetArenaCommand($this));
        $map->register("ffa", new SetTitleCommand($this));
        $map->register("ffa", new AdminCommand($this));
        $map->register("ffa", new AboutCommand($this));
        $map->register("ffa", new HelpCommand($this));
    }
    private function savePlayerInventory(Player $player): void {
        $name = $player->getName();

        if (!isset($this->savedInventories[$name])) {
            $this->savedInventories[$name] = $player->getInventory()->getContents(true);
            $this->savedArmor[$name] = $player->getArmorInventory()->getContents(true);
            $this->savedGameMode[$name] = $player->getGamemode();
        }
    }
    private function restorePlayerInventory(Player $player): void {
        $name = $player->getName();

        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();

        if (isset($this->savedInventories[$name])) {
            $player->getInventory()->setContents($this->savedInventories[$name]);
            unset($this->savedInventories[$name]);
        }

        if (isset($this->savedArmor[$name])) {
            $player->getArmorInventory()->setContents($this->savedArmor[$name]);
            unset($this->savedArmor[$name]);
        }

        if (isset($this->savedGameMode[$name])) {
            $player->setGamemode($this->savedGameMode[$name]);
            unset($this->savedGameMode[$name]);
        }
    }
    public function onJoinGameFFA(Player $player): void {
        $worldName = $this->getConfig()->get("arena");
        $world = $this->getServer()->getWorldManager()->getWorldByName($worldName);

        if ($world === null) {
            $this->getServer()->getWorldManager()->loadWorld($worldName);
            $world = $this->getServer()->getWorldManager()->getWorldByName($worldName);
        }

        if ($world === null) {
            $player->sendMessage("§cArena not found!");
            return;
        }

        if (isset($this->players[$player->getName()])) {
            $player->sendMessage("§cYa estás en el FFA!");
            return;
        }

        $this->savePlayerInventory($player);

        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();

        $player->teleport($world->getSafeSpawn());
        $player->setGamemode(GameMode::SURVIVAL());
        $player->setHealth($player->getMaxHealth());
        $player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());

        $this->giveFFAKit($player);

        $this->players[$player->getName()] = $player;

        $this->scoreboardManager->addScoreboard($player);

        $player->sendMessage("§a¡Has entrado al FFA!");
    }
    public function exitFFA(Player $player): void {
        if (!isset($this->players[$player->getName()])) {
            return;
        }

        $this->scoreboardManager->removeScoreboard($player);

        unset($this->players[$player->getName()]);

        $this->restorePlayerInventory($player);

        $defaultWorld = $this->getServer()->getWorldManager()->getDefaultWorld();
        if ($defaultWorld !== null) {
            $player->teleport($defaultWorld->getSafeSpawn());
        }

        $player->setHealth($player->getMaxHealth());
        $player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());

        $player->sendMessage("§c¡Has salido del FFA!");
    }

    public function onDeath(PlayerDeathEvent $event): void {
        $player = $event->getPlayer();
        $cause = $player->getLastDamageCause();

        if ($player->getWorld()->getFolderName() === $this->getConfig()->get("arena")) {

            $this->addDeath($player->getName());

            if ($cause instanceof EntityDamageByEntityEvent) {
                $killer = $cause->getDamager();
                if ($killer instanceof Player) {
                    $this->addKill($killer->getName());
                    $killer->sendMessage("§a+1 Kill | Víctima: §e" . $player->getName());
                }
            }

            $event->setDrops([]);
            $event->setXpDropAmount(0);

            $this->getScheduler()->scheduleDelayedTask(new class($this, $player) extends \pocketmine\scheduler\Task {
                private Main $plugin;
                private Player $player;

                public function __construct(Main $plugin, Player $player) {
                    $this->plugin = $plugin;
                    $this->player = $player;
                }

                public function onRun(): void {
                    if ($this->player->isOnline()) {
                        $this->plugin->exitFFA($this->player);
                    }
                }
            }, 20);
        }
    }

    public function onQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();

        if (isset($this->players[$player->getName()])) {
            $this->exitFFA($player);
        }
    }
    private function giveFFAKit(Player $player): void {
        $items = $this->getConfig()->get("items", ["diamond_sword", "steak"]);

        foreach ($items as $name) {
            $item = $this->getItemByString($name);
            if ($item !== null) {
                $player->getInventory()->addItem($item);
            }
        }
    }

    private function getItemByString(string $name) {
        return match ($name) {
            "diamond_sword" => VanillaItems::DIAMOND_SWORD(),
            "iron_sword" => VanillaItems::IRON_SWORD(),
            "stone_sword" => VanillaItems::STONE_SWORD(),
            "steak", "cooked_beef" => VanillaItems::STEAK()->setCount(32),
            "apple" => VanillaItems::APPLE()->setCount(16),
            "golden_apple" => VanillaItems::GOLDEN_APPLE()->setCount(5),
            default => null,
        };
    }
    public function onDamageEntityJoin(EntityDamageEvent $event): void {

        $entity = $event->getEntity();

        if (!$entity instanceof EntityJoinFFA) return;
        if (!$event instanceof EntityDamageByEntityEvent) return;

        $damager = $event->getDamager();
        if (!$damager instanceof Player) return;

        $event->cancel();

        $this->onJoinGameFFA($damager);
    }
    public function spawnEntityJoin(Player $player): void {
        $location = $player->getLocation();
        $skin = $player->getSkin();

        $npc = new EntityJoinFFA($location, $skin);
        $npc->setNameTag("§a§lToca para Jugar\n§eClick to Join FFA");
        $npc->spawnToAll();

        $this->saveNPC($location, $skin);
    }
    public function setArena(string $worldName): void {
        $this->getConfig()->set("arena", $worldName);
        $this->saveConfig();
    }
    private function saveNPC(Location $location, Skin $skin): void {
        $npcs = $this->getConfig()->get("npcs", []);

        $npcs[] = [
            "world" => $location->getWorld()->getFolderName(),
            "x" => $location->x,
            "y" => $location->y,
            "z" => $location->z,
            "yaw" => $location->yaw,
            "pitch" => $location->pitch,
            "skin" => base64_encode($skin->getSkinData())
        ];

        $this->getConfig()->set("npcs", $npcs);
        $this->saveConfig();
    }

    private function loadNPCs(): void {
        $npcs = $this->getConfig()->get("npcs", []);

        foreach ($npcs as $data) {
            $world = $this->getServer()->getWorldManager()->getWorldByName($data["world"]);
            if (!$world) continue;

            if (!isset($data["x"], $data["y"], $data["z"])) {
                $this->getLogger()->warning("NPC data incomplete, skipping...");
                continue;
            }

            $location = new Location(
                (float)$data["x"],
                (float)$data["y"],
                (float)$data["z"],
                $world,
                (float)($data["yaw"] ?? 0.0),
                (float)($data["pitch"] ?? 0.0)
            );

            if (!isset($data["skin"]) || empty($data["skin"])) {
                $this->getLogger()->warning("NPC without skin data, skipping...");
                continue;
            }

            $skinData = base64_decode($data["skin"]);
            if ($skinData === false) {
                $this->getLogger()->warning("Invalid skin data, skipping NPC...");
                continue;
            }

            $npc = new EntityJoinFFA($location, new Skin("Standard", $skinData));
            $npc->setNameTag("§a§lToca para Jugar\n§eClick to Join FFA");
            $npc->spawnToAll();
        }
    }
    public function addKill(string $player): void {
        $this->playerStats[$player]["kills"] = ($this->playerStats[$player]["kills"] ?? 0) + 1;
        $this->saveStats();
    }

    public function addDeath(string $player): void {
        $this->playerStats[$player]["deaths"] = ($this->playerStats[$player]["deaths"] ?? 0) + 1;
        $this->saveStats();
    }

    public function getPlayerKills(string $player): int {
        return $this->playerStats[$player]["kills"] ?? 0;
    }

    public function getPlayerDeaths(string $player): int {
        return $this->playerStats[$player]["deaths"] ?? 0;
    }

    private function saveStats(): void {
        $this->getConfig()->set("player_stats", $this->playerStats);
        $this->saveConfig();
    }

    private function loadStats(): void {
        $this->playerStats = $this->getConfig()->get("player_stats", []);
    }

}
