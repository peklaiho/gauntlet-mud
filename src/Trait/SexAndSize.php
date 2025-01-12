<?php
/**
 * Gauntlet MUD - Trait for sex and size
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Trait;

use Gauntlet\Enum\Sex;
use Gauntlet\Enum\Size;

trait SexAndSize
{
    protected Size $size = Size::Medium;
    protected Sex $sex = Sex::Neutral;

    public function getSex(): Sex
    {
        return $this->sex;
    }

    public function getSize(): Size
    {
        return $this->size;
    }

    public function setSex(Sex $val): void
    {
        $this->sex = $val;
    }

    public function setSize(Size $val): void
    {
        $this->size = $val;
    }
}
