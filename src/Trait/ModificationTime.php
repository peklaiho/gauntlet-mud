<?php
/**
 * Gauntlet MUD - Trait for modification time
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Trait;

trait ModificationTime
{
    protected int $modifiedTime;

    public function getModificationTime(): int
    {
        return $this->modifiedTime;
    }

    public function getTimeSinceModification(): int
    {
        return time() - $this->modifiedTime;
    }

    public function setModificationTime(int $time): void
    {
        $this->modifiedTime = $time;
    }
}
