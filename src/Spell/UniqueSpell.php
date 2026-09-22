<?php
/**
 * Gauntlet MUD - Spells with unique mechanics
 * Copyright (C) 2026 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Spell;

use Gauntlet\Act;
use Gauntlet\Item;
use Gauntlet\Living;
use Gauntlet\Renderer;
use Gauntlet\World;
use Gauntlet\Enum\Spell;

class UniqueSpell extends BaseSpell
{
    public function __construct(
        protected Spell $spell,
        protected float $manaCost
    ) {

    }

    public function manaCost(): float
    {
        return $this->manaCost;
    }

    public function findTarget(Living $caster, ?string $targetName): Living|Item|null
    {
        if ($this->spell == Spell::WordOfRecall) {
            return $caster;
        }

        throw new \RuntimeException('Attempt to cast unknown unique spell: ' . $this->spell->value);
    }

    public function cast(Living $caster, Living|Item $target): void
    {
        $act = SERVICE_CONTAINER->get(Act::class);
        $world = SERVICE_CONTAINER->get(World::class);
        $renderer = SERVICE_CONTAINER->get(Renderer::class);

        if ($this->spell == Spell::WordOfRecall) {
            $room = $world->getStartingRoom($target, false);
            $act->toRoom("@t disappears in a puff of smoke!", true, $target);
            $world->livingToRoom($target, $room);
            $act->toRoom("@a appears in a puff of smoke!", true, $target);
            $renderer->renderRoom($target, $room);
        } else {
            throw new \RuntimeException('Attempt to cast unknown unique spell: ' . $this->spell->value);
        }
    }
}
