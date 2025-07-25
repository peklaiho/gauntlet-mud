<?php
/**
 * Gauntlet MUD - Trait for modifiers
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Trait;

use Gauntlet\Enum\Modifier;

trait Modifiers
{
    protected array $mods = [];

    public function getMod(Modifier $mod): float
    {
        return $this->mods[$mod->value] ?? 0;
    }

    public function getMods(): array
    {
        return $this->mods;
    }

    public function setMod(Modifier $mod, float $val): void
    {
        $this->mods[$mod->value] = $val;
    }
}
