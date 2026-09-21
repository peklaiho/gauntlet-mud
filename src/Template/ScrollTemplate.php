<?php
/**
 * Gauntlet MUD - Template for scrolls
 * Copyright (C) 2026 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Template;

use Gauntlet\Enum\Spell;

class ScrollTemplate extends ItemTemplate
{
    protected Spell $spell;

    public function getSpell(): Spell
    {
        return $this->spell;
    }

    public function setSpell(Spell $value): void
    {
        $this->spell = $value;
    }
}
