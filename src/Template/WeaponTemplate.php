<?php
/**
 * Gauntlet MUD - Template for weapon items
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Template;

use Gauntlet\Trait\AttackType;
use Gauntlet\Trait\DamageType;

class WeaponTemplate extends ItemTemplate
{
    protected float $minDamage = 1;
    protected float $maxDamage = 1;

    use AttackType;
    use DamageType;

    public function getMinDamage(): float
    {
        return $this->minDamage;
    }

    public function getMaxDamage(): float
    {
        return $this->maxDamage;
    }

    public function setMinDamage(float $val): void
    {
        $this->minDamage = $val;
    }

    public function setMaxDamage(float $val): void
    {
        $this->maxDamage = $val;
    }

    // Required strength to wield it
    public function getRequiredStr(): int
    {
        return intval(round(BASE_ATTR + (($this->getWeight() - 1) * 2), 0, PHP_ROUND_HALF_DOWN));
    }
}
