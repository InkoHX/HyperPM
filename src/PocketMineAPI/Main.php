<?php

namespace PocketMineAPI;

use pocketmine\PocketMine;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\network\mcpe\protocol\LoginPacket;

use PocketMineAPI\entity\EntityBase;

class Main extends PluginBase implements Listener {

    public function onEnable() {
    	if(PocketMine::NAME != "PocketMine-MP") {
    		$this->getServer()->getLogger()->info("§c[PlayerHelper] PocketMine-MPのみこのプラグインは読み込まれます。");
    		return false;
    	}
        $this->getServer()->getPluginManager()->registerEvents($this,$this);
    }

    public function onCreation(PlayerCreationEvent $event) {
        $event->setPlayerClass(PlayerSession::class);
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
        }
    }
}