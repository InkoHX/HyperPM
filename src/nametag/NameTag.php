<?php

namespace nametag;

class NameTag {

    public $player;
    public $originalTag;
    public $tag;

    public function __construct(Player $player, $tag = ""){
        $this->player = $player;
        $this->originalTag = $player->getDisplayName();
        $this->tag = $tag;
    }

    public function getPlayer() :Player{
        return $this->player;
    }

    public function updateOriginalNameTag(string $originalTag) {
        $this->originalTag = $originalTag;
    }

    public function getOriginalNameTag() :string{
        return $this->originalTag;
    }

    public function setTag(string $tag) {
        $this->tag = $tag;
    }

    public function getTag() :string {
        return $this->tag;
    }
}