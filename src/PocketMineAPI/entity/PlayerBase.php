<?php

namespace PocketMineAPI\entity;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\entity\Skin;
use pocketmine\utils\UUID;
use pocketmine\level\Position;

use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerSkinPacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;

class PlayerBase extends EntityBase {

    protected $skin;
    protected $uuid;
    protected $iteminhand;
    protected $iteminoffhand;

    public function __construct(Position $position, float $yaw = 0.0, float $pitch = 0.0) {
        parent::__construct($position, $yaw, $pitch);

        $this->skin = new Skin("","","","","");
        $this->uuid = UUID::fromRandom();

        $this->sendSkin();
    }

    public function getUniqueId() {
        return $this->uuid;
    }

    public function setSkin(Skin $skin) {
        $this->skin = $skin;
    }

    public function getSkin() : Skin{
        return $this->skin;
    }

    public function sendSkin(array $targets = null) : void{
        $pk = new PlayerSkinPacket();
        $pk->uuid = $this->getUniqueId();
        $pk->skin = $this->getSkin();
        Server::getInstance()->broadcastPacket($targets ?? Server::getInstance()->getOnlinePlayers(), $pk);
    }

    public function spawnTo(Player $player) :bool{
        if(!parent::spawnTo($player)) {
            return false;
        }

        $this->function_a_08192($player);

        $pk = new AddPlayerPacket();
        $pk->uuid = $this->getUniqueId();
        $pk->username = $this->getName();
        $pk->entityRuntimeId = $this->getId();
        $pk->position = $this->asVector3();
        $pk->motion = $this->getMotion();
        $pk->pitch = $this->pitch;
        $pk->yaw = $this->yaw;
        $pk->headYaw = $this->headYaw;
        $pk->item = $this->getItemInHand();
        $pk->metadata = $this->propertyManager->getAll();
        $player->dataPacket($pk);

        $this->sendSkin([$player]);

        $this->function_v_183717($player);
        return true;
    }

    public function function_a_08192(Player $player) {
        $pk = new PlayerListPacket();
        $pk->type = PlayerListPacket::TYPE_ADD;
        $pk->entries = [PlayerListEntry::createAdditionEntry($this->uuid, $this->id, $this->getName(), $this->getName(), 0, $this->skin)];
        $player->dataPacket($pk);
    }

    public function function_v_183717(Player $player) {
        $pk = new PlayerListPacket();
        $pk->type = PlayerListPacket::TYPE_REMOVE;
        $pk->entries = [PlayerListEntry::createRemovalEntry($this->uuid)];
        $player->dataPacket($pk);
    }
}