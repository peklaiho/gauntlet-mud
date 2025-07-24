<?php
/**
 * Gauntlet MUD - Base class for players and monsters
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Enum\Attack;
use Gauntlet\Enum\Damage;
use Gauntlet\Enum\EqSlot;
use Gauntlet\Enum\Modifier;
use Gauntlet\Enum\Sex;
use Gauntlet\Enum\Size;

abstract class Living extends BaseObject
{
    protected ?Room $room = null;
    protected ?Living $target = null;
    protected ?Group $group = null;

    protected Collection $inventory;
    protected Collection $equipment;

    protected float $health;
    protected int $coins = 0;

    public function __construct()
    {
        $this->inventory = new Collection();
        $this->equipment = new Collection();
    }

    // Physical combat modifiers

    public function bonusToHit(): int
    {
        return $this->getMod(Modifier::Hit) + $this->getDex(false);
    }

    public function bonusToDodge(): int
    {
        return $this->getMod(Modifier::Dodge) + $this->getDex(false);
    }

    public function getBonusDamage(): float
    {
        return $this->getMod(Modifier::Damage) + ($this->getStr(false) * 0.4);
    }

    // Magic combat modifiers

    public function bonusToSpellHit(): int
    {
        return $this->getMod(Modifier::SpellHit) + $this->getInt(false);
    }

    public function bonusToSpellDodge(): int
    {
        return $this->getMod(Modifier::SpellDodge) + $this->getInt(false);
    }

    public function getBonusSpellDamage(): float
    {
        return $this->getMod(Modifier::SpellDamage) + ($this->getInt(false) * 2);
    }

    public function findCurrentSlot(Item $item): ?EqSlot
    {
        foreach (EqSlot::list() as $slot) {
            if ($this->getEqInSlot($slot) === $item) {
                return $slot;
            }
        }

        return null;
    }

    public function findEmptySlot(Item $item): ?EqSlot
    {
        if ($item->isWeapon()) {
            if ($this->getWeapon()) {
                return null;
            }

            return EqSlot::Wield;
        }

        foreach ($item->getTemplate()->getSlots() as $slot) {
            if (!$this->getEqInSlot($slot)) {
                return $slot;
            }
        }

        return null;
    }

    public function getEquipment(): Collection
    {
        return $this->equipment;
    }

    public function getEqInSlot(EqSlot $slot): ?Item
    {
        return $this->equipment->get($slot->value);
    }

    public function getHealth(): float
    {
        return $this->health;
    }

    public function getCoins(): int
    {
        return $this->coins;
    }

    public function getWeapon(): ?Item
    {
        return $this->equipment->get(EqSlot::Wield->value);
    }

    public function getMod(Modifier $mod): float
    {
        $sum = 0;

        foreach ($this->equipment->getAll() as $eq) {
            $sum += $eq->getTemplate()->getMod($mod);
        }

        return $sum;
    }

    public function getInventory(): Collection
    {
        return $this->inventory;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function getTarget(): ?Living
    {
        return $this->target;
    }

    public function getGroup(): ?Group
    {
        return $this->group;
    }

    public function hasKey(RoomExit $exit): bool
    {
        foreach ($this->inventory->getAll() as $item) {
            if ($item->getTemplate()->getId() == $exit->getTemplate()->getKeyId()) {
                return true;
            }
        }

        return false;
    }

    public function isMonster(): bool
    {
        return get_class($this) == Monster::class;
    }

    public function isPlayer(): bool
    {
        return get_class($this) == Player::class;
    }

    public function setHealth(float $val): void
    {
        $this->health = $val;
    }

    public function addCoins(int $val): int
    {
        $this->coins += $val;
        return $this->coins;
    }

    public function setCoins(int $val): void
    {
        $this->coins = $val;
    }

    public function setRoom(?Room $room): void
    {
        $this->room = $room;
    }

    public function setTarget(?Living $target): void
    {
        $this->target = $target;
    }

    public function setGroup(?Group $group): void
    {
        $this->group = $group;
    }

    // Attributes
    public function getStr(bool $includeBase = true): int
    {
        $val = $this->getMod(Modifier::Strength);

        if ($includeBase) {
            $val += BASE_ATTR;
        }

        return $val;
    }

    public function getDex(bool $includeBase = true): int
    {
        $val = $this->getMod(Modifier::Dexterity);

        if ($includeBase) {
            $val += BASE_ATTR;
        }

        return $val;
    }

    public function getInt(bool $includeBase = true): int
    {
        $val = $this->getMod(Modifier::Intelligence);

        if ($includeBase) {
            $val += BASE_ATTR;
        }

        return $val;
    }

    public function getCon(bool $includeBase = true): int
    {
        $val = $this->getMod(Modifier::Constitution);

        if ($includeBase) {
            $val += BASE_ATTR;
        }

        return $val;
    }

    public abstract function canSee(Living $other): bool;
    public abstract function canSeeItem(Item $item): bool;
    public abstract function canSeeRoom(): bool;
    public abstract function shouldFollow(Living $other): bool;

    public abstract function getName(): string;
    public abstract function getSex(): Sex;
    public abstract function getSize(): Size;
    public abstract function getWeight(): float;
    public abstract function getExperience(): int;
    public abstract function getLevel(): int;
    public abstract function getMaxHealth(): float;

    public abstract function getAttackType(): Attack;
    public abstract function getDamageType(): Damage;
    public abstract function getNumAttacks(): int;
    public abstract function getMinDamage(): float;
    public abstract function getMaxDamage(): float;

    public abstract function regenerate(): void;
}
