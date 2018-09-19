<?php

namespace PocketMineAPI;

use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\level\Position;

use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;

use PocketMineAPI\entity\EntityBase;
use PocketMineAPI\entity\EntryEntity;

class Main extends PluginBase implements Listener {

    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this,$this);
    }

    public function onCreation(PlayerCreationEvent $event) {
        $event->setPlayerClass(PlayerSession::class);
    }

    public function onJoin(PlayerJoinEvent $event) {
    	EntryEntity::spawnToEntryEntity($event->getPlayer());
    }

    public function onLevelChange(EntityLevelChangeEvent $event) {
    	//EntityBase::switchLevel($event);
    }

    public function onRecievePacket(DataPacketReceiveEvent $event){
        $player = $event->getPlayer();
        $packet = $event->getPacket();
        if($packet instanceof LoginPacket) {
            $player->deviceModel = $packet->clientData["DeviceModel"];
            $player->deviceOS = $packet->clientData["DeviceOS"];
        }elseif($packet instanceof InventoryTransactionPacket){
        	$transactionData = $packet->trData;
        	if($packet->transactionType === InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY) {
        		$entity = EntityBase::getEntity($player->getLevel(), $transactionData->entityRuntimeId);
        		if($entity instanceof EntityBase) {
        			$entity->interact($player);
        		}
        	}
        }
    }
}