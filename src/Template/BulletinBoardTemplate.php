<?php
/**
 * Gauntlet MUD - Template for bulletin boards
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Template;

use Gauntlet\Collection;

class BulletinBoardTemplate extends ItemTemplate
{
    protected Collection $messages;

    public function __construct()
    {
        $this->messages = new Collection();
    }

    public function getMessages(): Collection
    {
        return $this->messages;
    }
}
