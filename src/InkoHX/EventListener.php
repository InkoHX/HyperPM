<?php

namespace InkoHX;

use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\LoginPacket;
use PocketMineAPI\entity\EntityBase;
use PocketMineAPI\entity\EntryEntity;

class EventListener implements Listener
{
    public function onCreation(PlayerCreationEvent $event)
    {
        if (Main::ENABLE_CREATION) {
            $event->setPlayerClass(PlayerSession::class);
        }
    }

    public function onJoin(PlayerJoinEvent $event)
    {
        EntryEntity::spawnToEntryEntity($event->getPlayer());
    }

    public function onLevelChange(EntityLevelChangeEvent $event)
    {
        // EntryEntity::switchLevel($event); ???
    }

    public function onReceivePacket(DataPacketReceiveEvent $event)
    {
        $player = $event->getPlayer();
        $packet = $event->getPacket();
        if ($packet instanceof LoginPacket) {
            $player->deviceModel = $packet->clientData["DeviceModel"];
            $player->deviceOS = $packet->clientData["DeviceOS"];
        } elseif ($packet instanceof InventoryTransactionPacket) {
            $transactionData = $packet->trData;
            switch ($packet->transactionType) {
                case InventoryTransactionPacket::TYPE_USE_ITEM:
                    if ($player->clickTick < 3) {
                        $event->setCancelled();
                        return false;
                    }
                    $player->clickTick = 0;
                    break;
                case InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY:
                    $entity = EntityBase::getEntity($player->getLevel(), $transactionData->entityRuntimeId);
                    if ($entity instanceof EntityBase) {
                        $entity->interact($player);
                    }
                    break;
            }
        }
    }
}