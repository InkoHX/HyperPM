<?php

namespace nametag;

class Main extends PluginBase implements Listener {

    public static $sessions = [];

    public function onEnable() {};

    public function onLogin(PlayerLoginEvent $event) {
        $player = $event->getPlayer();
        self::createSession($player);
    }

    public function onSendPacket(DataPacketSendEvent $event) {
        $player = $event->getPlayer();
        $packet = $event->getPacket();
        if($packet instanceof AddPlayerPacket) {
            $changePlayer = self::getSessionByUUID($packet->uuid);
            if($changePlayer instanceof NameTag) {
                $add = new AddPlayerPacket();
                $add->uuid = $packet->uuid;
                $add->username = $changePlayer->getTag().$packet->username;
                $add->entityRuntimeId = $packet->entityRuntimeId;
                $add->position = $packet->position;
                $add->motion = $packet->motion;
                $add->yaw = $packet->yaw;
                $add->pitch = $packet->pitch;
                $add->item = $packet->item;
                $add->metadata = $packet->metadata;
                $player->dataPacket($add);

                //多分動かない
                /*$packet->username = $changePlayer->getTag().$packet->username;
                $player->dataPacket($packet);*/
            }
        }
    }

    public static function createSession(Player $player) {
        self::$sessions[$player->getName()] = new NameTag($player);
    }

    public static function getSessionByUUID(UUID $uuid) {
        $player = Server::getInstance()->getPlayerByUUID($uuid);
        if($player instanceof Player) {
            return self::getSessionByPlayer($player);
        }
        return null;
    }

    public static function getSessionByPlayer(Player $player){
        if(empty(self::$sessions[$player->getName()])) return null;
        return self::$sessions[$player->getName()];
    }