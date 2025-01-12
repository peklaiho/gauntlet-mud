<?php
/**
 * Gauntlet MUD - Trait for carrying capacity
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Trait;

use Gauntlet\Item;

trait CarryingCapacity
{
    public function getCarriedWeight(): float
    {
        $weight = 0;

        foreach ($this->inventory->getAll() as $obj) {
            $weight += $obj->getWeight();
        }

        foreach ($this->equipment->getAll() as $obj) {
            $weight += $obj->getWeight();
        }

        return $weight;
    }

    public function getCarryingCapacity(bool $whenEncumbered = false): float
    {
        $capacity = $this->getStr() * 4;

        // Ability to carry 20% extra when encumbered
        if ($whenEncumbered) {
            $capacity *= 1.2;
        }

        return $capacity;
    }

    public function canCarry(Item $item, bool $allowEncumbered): bool
    {
        return $item->getWeight() + $this->getCarriedWeight() <= $this->getCarryingCapacity($allowEncumbered);
    }

    public function isEncumbered(): bool
    {
        return $this->getCarriedWeight() > $this->getCarryingCapacity();
    }
}
