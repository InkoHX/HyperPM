<?php

namespace PocketMineAPI\entity\projectile;

use pocketmine\block\Block;
use pocketmine\entity\Entity;

use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\inventory\InventoryPickupArrowEvent;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\level\Level;
use pocketmine\level\particle\GenericParticle;
use pocketmine\math\RayTraceResult;
use pocketmine\math\VoxelRayTrace;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\timings\Timings;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\TakeItemEntityPacket;
use pocketmine\Player;

class Arrow extends Projectile
{
    public const NETWORK_ID = self::ARROW;

    public $width = 0.25;
    public $height = 0.25;

    protected $gravity = 0.04;
    protected $drag = 0.01;

    protected $damage = 1;

    protected $particleId = null;

    public function __construct(Level $level, CompoundTag $nbt, Entity $shootingEntity = null, bool $critical = false)
    {
        parent::__construct($level, $nbt, $shootingEntity);
        $this->setCritical($critical);
    }

    public function isCritical(): bool
    {
        return $this->getGenericFlag(self::DATA_FLAG_CRITICAL);
    }

    public function setCritical(bool $value = true)
    {
        $this->setGenericFlag(self::DATA_FLAG_CRITICAL, $value);
        if ($value) $this->particleId = null;
    }

    public function setParticleId(int $particleId)
    {
        $this->particleId = $particleId;
        $this->setGenericFlag(self::DATA_FLAG_CRITICAL, false);
    }

    public function getResultDamage(): int
    {
        $base = parent::getResultDamage();
        if ($this->isCritical()) {
            return ($base + mt_rand(0, (int)($base / 2) + 1));
        } else {
            return $base;
        }
    }

    public function entityBaseTick(int $tickDiff = 1): bool
    {
        if ($this->closed) {
            return false;
        }

        $hasUpdate = parent::entityBaseTick($tickDiff);

        if ($this->blockHit != null) $this->close();

        if ($this->particleId != null) {
            if ($this->level != null) {
                $particle = new GenericParticle($this, $this->particleId);
                $this->level->addParticle($particle);
            }
        }

        if ($this->age > 1200) {
            $this->flagForDespawn();
            $hasUpdate = true;
        }

        return $hasUpdate;
    }

    protected function onHit(ProjectileHitEvent $event): void
    {
        $this->setCritical(false);
        $this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_BOW_HIT);
    }

    protected function onHitBlock(Block $blockHit, RayTraceResult $hitResult): void
    {
        parent::onHitBlock($blockHit, $hitResult);
        $this->broadcastEntityEvent(EntityEventPacket::ARROW_SHAKE, 7); //7 ticks
    }
}