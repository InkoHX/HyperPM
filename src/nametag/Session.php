<?php

namespace nametag;

use pocketmine\Player;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\SetEntityDataPacket;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;

class Session extends Player {

    public function sendData($player, ?array $data = \null) : void{
        if(!\is_array($player)){
            $player = [$player];
        }

        $pk = new SetEntityDataPacket();
        $pk->entityRuntimeId = $this->getId();
        $pk->metadata = $data ?? $this->getDataPropertyManager()->getAll();

        // Temporary fix for player custom name tags visible
        $includeNametag = isset($data[self::DATA_NAMETAG]);
        if(($isPlayer = $this instanceof Player) and $includeNametag){
            $remove = new RemoveEntityPacket();
            $remove->entityUniqueId = $this->getId();
            $add = new AddPlayerPacket();
            $add->uuid = $this->getUniqueId();
            $add->username = $this->getNameTag();
            $add->entityRuntimeId = $this->getId();
            $add->position = $this->asVector3();
            $add->motion = $this->getMotion();
            $add->yaw = $this->yaw;
            $add->pitch = $this->pitch;
            $add->item = $this->getInventory()->getItemInHand();
            $add->metadata = $this->getDataPropertyManager()->getAll();
        }

        foreach($player as $p){
            if($p === $this){
                continue;
            }
            $p->dataPacket(clone $pk);

            if($isPlayer and $includeNametag){
                $p->sendDataPacket(clone $remove);
                $p->sendDataPacket(clone $add);
            }
        }

        if($this instanceof Player){
            $this->dataPacket($pk);
        }
    }
}