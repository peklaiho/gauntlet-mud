<?php
/**
 * Gauntlet MUD - Single-target damage spells
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Spell;

use Gauntlet\Act;
use Gauntlet\Fight;
use Gauntlet\Item;
use Gauntlet\Living;
use Gauntlet\Enum\Spell;
use Gauntlet\Util\LivingFinder;

class DamageSpell extends BaseSpell
{
    public function __construct(
        protected Spell $spell,
        protected float $manaCost,
        protected float $baseDamage,
        protected float $intDamage
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
            ->excludeSelf()
            ->find($targetName);
        return $target;
    }

    public function cast(Living $caster, Living|Item|null $target): void
    {
        $act = SERVICE_CONTAINER->get(Act::class);
        $fight = SERVICE_CONTAINER->get(Fight::class);

        if ($fight->canHitMagic($caster, $target)) {
            $damage = $this->baseDamage +
                ($caster->getInt(false) * $this->intDamage) +
                $caster->getBonusSpellDamage();

            $act->toChar("Your spell hits @T with devastating effect!", $caster, null, $target);
            $act->toVict("Spell from @t hits you with devastating effect!", false, $caster, null, $target);
            $act->toRoom("Spell from @t hits @T with devastating effect!", false, $caster, null, $target, true);
        } else {
            $damage = 0;

            $act->toChar("Your spell misses @M!", $caster, null, $target);
            $act->toVict('Spell from @t misses you!', false, $caster, null, $target);
            $act->toRoom('Spell from @t misses @T!', false, $caster, null, $target, true);
        }

        $fight->specialAttack($caster, $target, $damage);
    }
}
