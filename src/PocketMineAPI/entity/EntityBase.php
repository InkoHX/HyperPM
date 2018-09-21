<?php

namespace PocketMineAPI\entity;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityIds;
use pocketmine\entity\DataPropertyManager;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\item\Item;

use pocketmine\event\entity\EntityLevelChangeEvent;

use pocketmine\network\mcpe\protocol\RemoveEntityPacket;
use pocketmine\network\mcpe\protocol\SetEntityDataPacket;

class EntityBase implements EntityIds{

    protected static $entries = [];

    protected $hasSpawned = [];

    protected $id;
    protected $pos;
    protected $motion;

    public $yaw = 0;
    public $headYaw = 0;
    public $pitch = 0;

    public $propertyManager;
    public $temporalVector;

    public $name = "";

    public function __construct(Position $position, float $yaw = 0.0, float $pitch = 0.0) {
        $this->id = Entity::$entityCount++;

        $this->propertyManager = new DataPropertyManager();
        $this->propertyManager->setLong(Entity::DATA_FLAGS, 0);
        $this->propertyManager->setShort(Entity::DATA_MAX_AIR, 400);
        $this->propertyManager->setString(Entity::DATA_NAMETAG, "");
        $this->propertyManager->setLong(Entity::DATA_LEAD_HOLDER_EID, -1);
        $this->propertyManager->setFloat(Entity::DATA_SCALE, 1);
        $this->propertyManager->setShort(Entity::DATA_AIR, 300);

        $this->temporalVector = new Vector3();
        $this->pos = $position->asVector3();;
        $this->level = $position->getLevel();
        $this->yaw = $yaw;
        $this->headYaw = $yaw;
        $this->pitch = $pitch;
        $this->motion = new Vector3(0,0,0);

        $this->iteminhand = Item::get(0);
        $this->iteminoffhand = Item::get(0);

        self::$entries[$this->level->getName()][$this->getId()] = $this;
    }

    public function getId() :int{
        return $this->id;
    }

    public function getName() :string{
        return $this->name;
    }

    public function getLevel() :Level{
        return $this->level;
    }

    public function asVector3() :Vector3{
        return $this->pos;
    }

    public function getMotion() :Vector3{
        return $this->motion;
    }

    public function setItemInHand(Item $item) {
        $this->iteminhand = $item;
    }

    public function getItemInHand() : Item{
        return $this->iteminhand;
    }

    public function setItemInOffHand(Item $item) {
        $this->iteminoffhand = $item;
    }

    public function getItemInOffHand() : Item{
        return $this->iteminoffhand;
    }

    public function setNameTag(string $name) {
        $this->propertyManager->setString(Entity::DATA_NAMETAG, $name);
    }

    public function getNameTag() :string{
        return $this->propertyManager->getString(Entity::DATA_NAMETAG);
    }

    public function setDataFlag(int $propertyId, int $flagId, bool $value = true, int $propertyType = Entity::DATA_TYPE_LONG){
        if($this->getDataFlag($propertyId, $flagId) !== $value){
            $flags = (int) $this->propertyManager->getPropertyValue($propertyId, $propertyType);
            $flags ^= 1 << $flagId;
            $this->propertyManager->setPropertyValue($propertyId, $propertyType, $flags);
        }
    }

    public function getDataFlag(int $propertyId, int $flagId) :bool{
        return (((int) $this->propertyManager->getPropertyValue($propertyId, -1)) & (1 << $flagId)) > 0;
    }

    public function updateData() {
        $pk = new SetEntityDataPacket();
        $pk->entityRuntimeId = $this->getId();
        $pk->metadata = $this->propertyManager->getAll();
        Server::getInstance()->broadcastPacket($this->getViewers(), $pk);
    }

    public function spawnToAll() : void{
        foreach($this->level->getPlayers() as $player) {
            $this->spawnTo($player);
        }
    }

    public function spawnTo(Player $player) :bool{
        /*if($this->level->getEntity($player->getId()) == null) {
            return false;
        }*/

        $this->hasSpawned[$player->getId()] = $player;
        return true;
    }

    public function despawnFromAll() :void{
        foreach ($this->hasSpawned as $id => $player) {
            $this->despawnFrom($player);
        }
    }

    public function despawnFrom(Player $player) :void{
        $pk = new RemoveEntityPacket();
        $pk->entityUniqueId = $this->getId();
        $player->dataPacket($pk);

        unset($this->hasSpawned[$player->getId()]);
    }

    public function getViewers() :array{
        return $this->hasSpawned;
    }

    public function interact(Player $player) {
        return false;
    }

    public static function getEntity(Level $level, int $id) {
        if(isset(self::$entries[$level->getName()])) {
            if(isset(self::$entries[$level->getName()][$id])) {
                return self::$entries[$level->getName()][$id];
            }
        }
        return null;
    }

    public static function getEntityById(int $id) {
        foreach (self::$entries as $level => $aaa) {
            if(isset($aaa[$id])) {
                return $aaa[$id];
            }
        }
        return null;
    }

    public static function getEntitiesByLevel(Level $level) {
        $d = [];
        if(self::isExistenceEntity($level)) {
            foreach(self::$entries[$level->getName()] as $id => $entity) {
                $d[] = $entity;
            }
        }
        return $d;
    }

    public static function switchLevel(EntityLevelChangeEvent $ev) :void{
        $player = $ev->getEntity();
        if($player instanceof Player) {
            foreach (self::getEntitiesByLevel($ev->getOrigin()) as $key => $entity) {
                $entity->despawnFrom($player);
            }
            foreach (self::getEntitiesByLevel($ev->getTarget()) as $key => $entity) {
                $entity->spawnTo($player);
            }
        }
    }

    public static function isExistenceEntity(Level $level) {
        return isset(self::$entries[$level->getName()]);
    }
}