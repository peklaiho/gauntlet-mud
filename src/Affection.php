<?php
/**
 * Gauntlet MUD - Class for affections
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Enum\AffectionType;
use Gauntlet\Enum\Skill;
use Gauntlet\Enum\Spell;
use Gauntlet\Trait\Modifiers;

class Affection
{
    use Modifiers;

    public function __construct(
        protected AffectionType $type,
        protected Skill|Spell $source,
        protected int $until
    ) {

    }

    public function getType(): AffectionType
    {
        return $this->type;
    }

    public function getSource(): Skill|Spell
    {
        return $this->source;
    }

    public function getUntil(): int
    {
        return $this->until;
    }
}
