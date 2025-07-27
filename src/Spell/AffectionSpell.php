<?php
/**
 * Gauntlet MUD - Spells that create affections
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Spell;

use Gauntlet\Affection;
use Gauntlet\Item;
use Gauntlet\Living;
use Gauntlet\Enum\AffectionType;
use Gauntlet\Enum\Modifier;
use Gauntlet\Enum\Spell;
use Gauntlet\Util\LivingFinder;

class AffectionSpell extends BaseSpell
{
    public function __construct(
        protected Spell $spell,
        protected float $manaCost,
        protected array $mods,
        protected int $duration
    ) {

    }

    public function manaCost(): float
    {
        return $this->manaCost;
    }

    public function findTarget(Living $caster, string $targetName): Living|Item|null
    {
        $lists = [$caster->getRoom()->getLiving()];
        $target = (new LivingFinder($caster, $lists))
            ->find($targetName);
        return $target;
    }

    public function cast(Living $caster, Living|Item|null $target): void
    {
        $aff = new Affection(AffectionType::Spell, $this->spell, time() + $this->duration);
        foreach ($this->mods as $modName => $value) {
            $aff->setMod(Modifier::from($modName), $value);
        }
        $target->addAffection($aff);
    }
}
