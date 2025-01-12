<?php
/**
 * Gauntlet MUD - Trait for creation time
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Trait;

trait CreationTime
{
    protected int $creationTime;

    public function getCreationTime(): int
    {
        return $this->creationTime;
    }

    public function getTimeSinceCreation(): int
    {
        return time() - $this->creationTime;
    }

    public function setCreationTime(int $time): void
    {
        $this->creationTime = $time;
    }
}
