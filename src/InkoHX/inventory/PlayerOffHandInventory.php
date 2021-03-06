<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace InkoHX\inventory;

use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\InventoryContentPacket;
use pocketmine\network\mcpe\protocol\InventorySlotPacket;
use pocketmine\Player;

use pocketmine\inventory\BaseInventory;

class PlayerOffHandInventory extends BaseInventory
{
    /** @var Player */
    protected $holder;

    public function __construct(Player $holder)
    {
        $this->holder = $holder;
        parent::__construct();
    }

    public function getName(): string
    {
        return "OffHand";
    }

    public function getDefaultSize(): int
    {
        return 1;
    }

    public function getHolder(): Player
    {
        return $this->holder;
    }

    public function setOffHand(Item $item): void
    {
        $this->setItem(0, $item);
    }

    public function setSize(int $size)
    {
        throw new \BadMethodCallException("OffHand can only carry one item at a time");
    }

    public function sendSlot(int $index, $target): void
    {
        if ($target instanceof Player) {
            $target = [$target];
        }

        /** @var Player[] $target */

        if (($k = array_search($this->holder, $target, true)) !== false) {
            $pk = new InventorySlotPacket();
            $pk->windowId = $target[$k]->getWindowId($this);
            $pk->inventorySlot = $index;
            $pk->item = $this->getItem($index);
            $target[$k]->sendDataPacket($pk);
            unset($target[$k]);
        }
        if (!empty($target)) {
            $pk = new MobEquipmentPacket();
            $pk->entityRuntimeId = $this->getHolder()->getId();
            $pk->inventorySlot = $pk->hotbarSlot = $this->getItem($index);
            $this->holder->getLevel()->getServer()->broadcastPacket($target, $pk);
        }
    }

    public function sendContents($target): void
    {
        if ($target instanceof Player) {
            $target = [$target];
        }

        if (($k = array_search($this->holder, $target, true)) !== false) {
            $pk = new InventoryContentPacket();
            $pk->windowId = $target[$k]->getWindowId($this);
            $pk->items = $this->getContents(true);
            $target[$k]->sendDataPacket($pk);
            unset($target[$k]);
        }
        if (!empty($target)) {
            $pk = new MobEquipmentPacket();
            $pk->entityRuntimeId = $this->getHolder()->getId();
            $pk->inventorySlot = $pk->hotbarSlot = $this->getItem(0);
            $this->holder->getLevel()->getServer()->broadcastPacket($target, $pk);
        }
    }

    /**
     * @return Player[]
     */
    public function getViewers(): array
    {
        return array_merge(parent::getViewers(), $this->holder->getViewers());
    }
}