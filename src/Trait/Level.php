<?php
/**
 * Gauntlet MUD - Trait for level
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Trait;

trait Level
{
    protected int $level = 1;

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $val): void
    {
        $this->level = $val;
    }
}
