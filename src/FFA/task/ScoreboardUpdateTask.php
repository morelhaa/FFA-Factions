<?php

namespace FFA\task;

use FFA\Main;
use pocketmine\scheduler\Task;

class ScoreboardUpdateTask extends Task {

    private Main $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function onRun(): void {
        $this->plugin->getScoreboardManager()->updateAll();
    }
}
