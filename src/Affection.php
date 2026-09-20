<?php
/**
 * Gauntlet MUD - Class for affections
 * Copyright (C) 2017-2026 Pekka Laiho
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

    protected int $elapsedTicks = 0;

    public function __construct(
        protected Living|Item $owner,
        protected AffectionType $type,
        protected Skill|Spell $source,
        protected int $totalTicks,
        protected ?string $endMessage = null
    ) {

    }

    public function getOwner(): Living|Item
    {
        return $this->owner;
    }

    public function getType(): AffectionType
    {
        return $this->type;
    }

    public function getSource(): Skill|Spell
    {
        return $this->source;
    }

    public function getTotalTicks(): int
    {
        return $this->totalTicks;
    }

    public function getEndMessage(): ?string
    {
        return $this->endMessage;
    }

    public function getElapsedTicks(): int
    {
        return $this->elapsedTicks;
    }

    public function setElapsedTicks(int $value): void
    {
        $this->elapsedTicks = $value;
    }

    // Return true if this affection finishes
    public function tick(): bool
    {
        $this->elapsedTicks++;

        return $this->elapsedTicks >= $this->totalTicks;
    }
}
