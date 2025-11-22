<?php

namespace FFA;

use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\player\Player;

class ScoreboardManager {

    private Main $plugin;
    private array $scoreboards = [];

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function addScoreboard(Player $player): void {
        $this->removeScoreboard($player);

        $objectiveName = "ffascore_" . $player->getName();
        $this->scoreboards[$player->getName()] = $objectiveName;

        $packet = new SetDisplayObjectivePacket();
        $packet->displaySlot = "sidebar";
        $packet->objectiveName = $objectiveName;
        $packet->displayName = $this->plugin->getConfig()->get("scoreboard_title", "§l§aFFA");
        $packet->criteriaName = "dummy";
        $packet->sortOrder = 0;

        $player->getNetworkSession()->sendDataPacket($packet);

        $this->updateScoreboard($player);
    }

    public function updateScoreboard(Player $player): void {
        if (!isset($this->scoreboards[$player->getName()])) {
            return;
        }

        $objectiveName = $this->scoreboards[$player->getName()];
        $kills = $this->plugin->getPlayerKills($player->getName());
        $deaths = $this->plugin->getPlayerDeaths($player->getName());
        $kdr = $deaths > 0 ? round($kills / $deaths, 2) : $kills;
        $playersInArena = count($this->plugin->players);

        $lines = [
            7 => " §r",
            8 => " §fKills: §a{$kills}",
            9 => " §fDeaths: §c{$deaths}",
            10 => " §fK/D: §e{$kdr}",
            11 => " §r ",
            12 => " §fJugadores: §b{$playersInArena}",
            13 => " §r  ",
            14 => " §ewww.tuservidor.com",
            15 => " §r   "
        ];

        $packet = new SetScorePacket();
        $packet->type = SetScorePacket::TYPE_CHANGE;

        $entries = [];
        foreach ($lines as $score => $line) {
            $entry = new ScorePacketEntry();
            $entry->objectiveName = $objectiveName;
            $entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
            $entry->customName = $line;
            $entry->score = $score;
            $entry->scoreboardId = $score;
            $entries[] = $entry;
        }

        $packet->entries = $entries;
        $player->getNetworkSession()->sendDataPacket($packet);
    }

    public function removeScoreboard(Player $player): void {
        if (!isset($this->scoreboards[$player->getName()])) {
            return;
        }

        $packet = new RemoveObjectivePacket();
        $packet->objectiveName = $this->scoreboards[$player->getName()];
        $player->getNetworkSession()->sendDataPacket($packet);

        unset($this->scoreboards[$player->getName()]);
    }

    public function updateAll(): void {
        foreach ($this->scoreboards as $playerName => $objectiveName) {
            $player = $this->plugin->getServer()->getPlayerByPrefix($playerName);

            if ($player !== null && $player->isOnline()) {
                $this->updateScoreboard($player);
            } else {
                unset($this->scoreboards[$playerName]);
            }
        }
    }
}
