<?php

namespace nametag;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;

class Main extends PluginBase implements Listener {

    public function onEnable() {};

    public function onCreation(PlayerCreationEvent $event) {
        $event->setPlayerClass(Session::class);
    }
}