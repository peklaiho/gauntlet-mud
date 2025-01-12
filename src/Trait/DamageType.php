<?php
/**
 * Gauntlet MUD - Trait for damage type
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Trait;

use Gauntlet\Enum\Damage;

trait DamageType
{
    protected Damage $damageType = Damage::Physical;

    public function getDamageType(): Damage
    {
        return $this->damageType;
    }

    public function setDamageType(Damage $type): void
    {
        $this->damageType = $type;
    }
}
