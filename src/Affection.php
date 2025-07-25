<?php
/**
 * Gauntlet MUD - Class for affections
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Closure;

use Gauntlet\Enum\AffectionType;
use Gauntlet\Enum\Skill;
use Gauntlet\Enum\Spell;
use Gauntlet\Trait\Modifiers;

class Affection
{
    use Modifiers;

    protected ?Closure $callback = null;

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

    public function getCallback(): ?Closure
    {
        return $this->callback;
    }

    public function setCallback(?Closure $callback): void
    {
        $this->callback = $callback;
    }
}
