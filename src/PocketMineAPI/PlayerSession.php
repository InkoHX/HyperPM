<?php

namespace PocketMineAPI;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\SetEntityDataPacket;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;
use pocketmine\network\mcpe\protocol\types\ContainerIds;

use PocketMineAPI\inventory\PlayerOffHandInventory;

class PlayerSession extends Player
{

    public const OS_ANDROID = 1;
    public const OS_IOS = 2;
    public const OS_MAC = 3;
    public const OS_FIREOS = 4;
    public const OS_GEARVR = 5;
    public const OS_HOLOLENS = 6;
    public const OS_WINDOWS = 7;
    public const OS_WIN32 = 8;
    public const OS_DEDICATED = 9;
    public const OS_ORBIS = 10;
    public const OS_NX = 11;

    public $deviceModel;
    public $deviceOS;

    public $clickTick = 0;

    public function __construct(Server $server, NetworkSession $session)
    {
        parent::__construct($server, $session);
        $this->offHandInventory = new PlayerOffHandInventory($this);
    }

    public function getOffHandInventory(): PlayerOffHandInventory
    {
        return $this->offHandInventory;
    }

    public function sendData($player, ?array $data = \null): void
    {
        if (!\is_array($player)) {
            $player = [$player];
        }

        $pk = new SetEntityDataPacket();
        $pk->entityRuntimeId = $this->getId();
        $pk->metadata = $data ?? $this->getDataPropertyManager()->getAll();

        // Temporary fix for player custom name tags visible
        $includeNametag = isset($data[self::DATA_NAMETAG]);
        if (($isPlayer = $this instanceof Player) and $includeNametag) {
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

        foreach ($player as $p) {
            if ($p === $this) {
                continue;
            }
            $p->dataPacket(clone $pk);

            if ($isPlayer and $includeNametag) {
                $p->sendDataPacket(clone $remove);
                $p->sendDataPacket(clone $add);
            }
        }

        if ($this instanceof Player) {
            $this->dataPacket($pk);
        }
    }

    public function onUpdate(int $currentTick): bool
    {
        $this->clickTick++;
        return parent::onUpdate($currentTick);
    }

    protected function addDefaultWindows()
    {
        parent::addDefaultWindows();

        $this->offHandInventory = new PlayerOffHandInventory($this);
        $this->addWindow($this->offHandInventory, ContainerIds::OFFHAND, true);
    }

    public function getDeviceModel()
    {
        return $this->deviceModel;
    }

    public function getDeviceOS()
    {
        return $this->deviceOS;
    }
}