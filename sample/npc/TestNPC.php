<?php

namespace Sample\npc;

use pocketmine\Player;
use pocketmine\level\Position;

class TestNPC extends PlayerBase {

	public function __construct(Position $position, float $yaw = 0.0, float $pitch = 0.0) {
		parent::__construct($position, $yaw, $pitch);

		$this->name = "TestNPC";
	}

	public function interact(Player $player) {
		$player->sendMessage("gggagagagag");
	}
}