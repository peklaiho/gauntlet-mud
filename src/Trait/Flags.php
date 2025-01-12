<?php
/**
 * Gauntlet MUD - Trait for flags
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Trait;

trait Flags
{
    protected array $flags = [];

    public function addFlag($flag): void
    {
        $this->flags[] = $flag;
    }

    public function getFlags(): array
    {
        return $this->flags;
    }

    public function hasFlag($flag): bool
    {
        return in_array($flag, $this->flags);
    }

    public function hasAnyFlag(...$flags): bool
    {
        foreach ($flags as $flag) {
            if (in_array($flag, $this->flags)) {
                return true;
            }
        }

        return false;
    }

    public function renderFlags(): string
    {
        return implode(', ', array_map(fn ($a) => ucfirst($a->value), $this->flags));
    }
}
