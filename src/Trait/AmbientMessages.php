<?php
/**
 * Gauntlet MUD - Trait for ambient messages
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Trait;

use Gauntlet\AmbientMessage;

trait AmbientMessages
{
    protected array $ambientMessages = [];

    public function addAmbientMessage(AmbientMessage $msg): void
    {
        $this->ambientMessages[] = $msg;
    }

    public function getAmbientMessages(): array
    {
        return $this->ambientMessages;
    }
}
