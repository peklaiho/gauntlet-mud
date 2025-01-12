<?php
/**
 * Gauntlet MUD - Template for container items
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Template;

class ContainerTemplate extends ItemTemplate
{
    protected float $capacity = 0;

    public function getCapacity(): float
    {
        return $this->capacity;
    }

    public function setCapacity(float $val): void
    {
        $this->capacity = $val;
    }
}
