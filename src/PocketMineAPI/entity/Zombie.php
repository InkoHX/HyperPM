<?php

namespace PocketMineAPI\entity;

use pocketmine\Player;
use pocketmine\level\Position;

use pocketmine\network\mcpe\protocol\AddEntityPacket;

class Zombie extends EntityBase {

	public function spawnTo(Player $player) :bool{
		if(parent::spawnTo($player)) {
			$pk = new AddEntityPacket();
			$pk->entityRuntimeId = $this->getId();
			$pk->type = self::ZOMBIE;
			$pk->position = $this->asVector3();
			$pk->motion = $this->getMotion();
			$pk->pitch = $this->pitch;
			$pk->yaw = $this->yaw;
			$pk->headYaw = $this->headYaw;
			$pk->metadata = $this->propertyManager->getAll();
			$player->dataPacket($pk);
			return false;
		}
		return true;
	}
}