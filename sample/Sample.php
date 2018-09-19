<?php

namespace Sample;

use PocketMineAPI\entity\Zombie;
use PocketMineAPI\entity\Creeper;
use Sample\npc\TestNPC;

class Sample extends PluginBase implements Listener {

	public function onEnable() {
		$world = Server::getInstance()->getLevelByName("world");
        EntryEntity::addEntry(new Zombie(new Position(201, 6, 298, $world)));
        EntryEntity::addEntry(new Creeper(new Position(203, 6, 298, $world)));
        EntryEntity::addEntry(new TestNPC(new Position(205, 6, 298, $world)));
    }