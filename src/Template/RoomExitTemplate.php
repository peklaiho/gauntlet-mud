<?php
/**
 * Gauntlet MUD - Template for room exits
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Template;

class RoomExitTemplate extends BaseTemplate
{
    protected ?int $keyId = null;
    protected ?string $doorName = null;

    public function __construct(
        protected int $roomId
    ) {

    }

    public function getRoomId(): int
    {
        return $this->roomId;
    }

    public function getKeyId(): ?int
    {
        return $this->keyId;
    }

    public function getDoorName(): ?string
    {
        return $this->doorName;
    }

    public function setKeyId(?int $val): void
    {
        $this->keyId = $val;
    }

    public function setDoorName(?string $val): void
    {
        $this->doorName = $val;
    }
}
