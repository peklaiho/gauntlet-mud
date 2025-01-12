<?php
/**
 * Gauntlet MUD - Trait for magic number
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Trait;

trait MagicNumber
{
    protected int $magicNumber;

    public function getMagicNumber(): int
    {
        return $this->magicNumber;
    }

    public function setMagicNumber(int $num): void
    {
        $this->magicNumber = $num;
    }
}
