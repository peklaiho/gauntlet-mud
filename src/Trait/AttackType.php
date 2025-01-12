<?php
/**
 * Gauntlet MUD - Trait for attack types
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Trait;

use Gauntlet\Enum\Attack;

trait AttackType
{
    protected Attack $attackType = Attack::Hit;

    public function getAttackType(): Attack
    {
        return $this->attackType;
    }

    public function setAttackType(Attack $type): void
    {
        $this->attackType = $type;
    }
}
