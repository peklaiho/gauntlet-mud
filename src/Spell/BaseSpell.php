<?php
/**
 * Gauntlet MUD - Base class for all spells
 * Copyright (C) 2017-2026 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Spell;

use Gauntlet\Item;
use Gauntlet\Living;

abstract class BaseSpell
{
    protected bool $harmful = false;

    public function isHarmful(): bool
    {
        return $this->harmful;
    }

    public function setHarmful(bool $value): void
    {
        $this->harmful = $value;
    }

    public abstract function manaCost(): float;
    public abstract function findTarget(Living $caster, ?string $targetName): Living|Item|null;
    public abstract function cast(Living $caster, Living|Item $target): void;
}
