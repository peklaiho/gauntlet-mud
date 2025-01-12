<?php
/**
 * Gauntlet MUD - Template for items
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Template;

use Gauntlet\Enum\EqSlot;

class ItemTemplate extends BaseTemplate
{
    protected float $weight = 0;
    protected int $cost = 0;
    protected array $slots = [];

    public function addSlot(EqSlot $slot): void
    {
        $this->slots[] = $slot;
    }

    public function getWeight(): float
    {
        return $this->weight;
    }

    public function getCost(): int
    {
        return $this->cost;
    }

    public function getSlots(): array
    {
        return $this->slots;
    }

    public function setWeight(float $val): void
    {
        $this->weight = $val;
    }

    public function setCost(int $val): void
    {
        $this->cost = $val;
    }
}
