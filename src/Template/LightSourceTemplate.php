<?php
/**
 * Gauntlet MUD - Template for light sources
 * Copyright (C) 2026 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Template;

class LightSourceTemplate extends ItemTemplate
{
    // -1 = unlimited
    protected int $fuel = 0;

    public function getFuel(): int
    {
        return $this->fuel;
    }

    public function hasUnlimitedFuel(): bool
    {
        return $this->fuel < 0;
    }

    public function setFuel(int $val): void
    {
        $this->fuel = $val;
    }
}
