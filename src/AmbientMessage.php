<?php
/**
 * Gauntlet MUD - Ambient message
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Enum\Sex;

class AmbientMessage
{
    public function __construct(
        protected string $roomMsg,
        protected ?string $victimMsg = null,
        protected ?Sex $victimSex = null
    ) {

    }

    public function getRoomMsg(): string
    {
        return $this->roomMsg;
    }

    public function getVictimMsg(): ?string
    {
        return $this->victimMsg;
    }

    public function getVictimSex(): ?Sex
    {
        return $this->victimSex;
    }
}
