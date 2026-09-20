<?php
/**
 * Gauntlet MUD - Trait for temporary player starting room
 * Copyright (C) 2017-2026 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Trait;

trait TemporaryPlayerRoom
{
    protected ?int $startingZoneId = null;
    protected ?int $startingRoomId = null;

    public function getStartingZoneId(): ?int
    {
        return $this->startingZoneId;
    }

    public function getStartingRoomId(): ?int
    {
        return $this->startingRoomId;
    }

    public function setStartingZoneId(?int $value): void
    {
        $this->startingZoneId = $value;
    }

    public function setStartingRoomId(?int $value): void
    {
        $this->startingRoomId = $value;
    }
}
