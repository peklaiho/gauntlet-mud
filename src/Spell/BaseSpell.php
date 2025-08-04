<?php
/**
 * Gauntlet MUD - Base class for all spells
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Spell;

use Gauntlet\Item;
use Gauntlet\Living;

abstract class BaseSpell
{
    public abstract function manaCost(): float;
    public abstract function findTarget(Living $caster, string $targetName): Living|Item|null;
    public abstract function cast(Living $caster, Living|Item $target): void;
}
