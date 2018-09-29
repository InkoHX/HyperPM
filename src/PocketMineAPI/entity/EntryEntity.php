<?php

namespace PocketMineAPI\entity;

use pocketmine\Player;

class EntryEntity {

    protected static $entry = [];

    public static function addEntry(EntityBase $entity, $key = null) {
        if($key == null) {
            self::$entry[] = $entity;
        }else{
            self::$entry[$key] = $entity;
        }
    }

    public static function getEntry($key) {
        if(empty(self::$entry[$key])) return null;
        return self::$entry[$key];
    }

    public static function spawnToEntryEntity(Player $player) {
        foreach(self::$entry as $key => $entity) {
            if($entity->getLevel() === $player->getLevel()) {
                $entity->spawnTo($player);
            }
        }
    }
}