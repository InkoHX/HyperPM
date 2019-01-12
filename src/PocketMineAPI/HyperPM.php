<?php

namespace PocketMineAPI;

use pocketmine\plugin\PluginBase;
use pocketmine\Server;

class HyperPM
{
    public const ENABLE_CREATION = true;

    /**
     * @param PluginBase $base
     */
    public static function init(PluginBase $base)
    {
        Server::getInstance()->getPluginManager()->registerEvents(new EventListener(), $base);
    }
}
