<?php
/**
 * Gauntlet MUD - Magic functions
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Enum\Spell;
use Gauntlet\Util\Random;

class Magic
{
    public function __construct(
        protected Fight $fight,
        protected Act $act
    ) {

    }

    // Combat modifiers for spells, similar to ones in Fight.php

    public function chanceToHit(Living $attacker, Living $victim): int
    {
        // Base chance to hit
        $toHit = BASE_TO_HIT;

        // Add hit bonus from attacker
        $toHit += $attacker->bonusToSpellHit();

        // Subtract dodge bonus from victim
        $toHit -= $victim->bonusToSpellDodge();

        // Make sure chance to hit is within bounds
        return min(max($toHit, MIN_TO_HIT), MAX_TO_HIT);
    }

    // True if attacker can successfully hit victim
    public function canHit(Living $attacker, Living $victim): bool
    {
        $toHit = $this->chanceToHit($attacker, $victim);

        return Random::percent($toHit);
    }

    public function castDamageSpell(Living $attacker, Living $victim, Spell $spell): void
    {
        $this->damageSpellInitMessage($attacker, $victim, $spell);

        if ($this->canHit($attacker, $victim)) {
            $damage = $this->getSpellDamage($spell) + $attacker->getBonusSpellDamage();
            $this->damageSpellHitMessage($attacker, $victim, $spell);
        } else {
            $damage = 0;
            $this->damageSpellMissMessage($attacker, $victim, $spell);
        }

        $this->fight->specialAttack($attacker, $victim, $damage);
    }

    private function getSpellDamage(Spell $spell): float
    {
        // TODO: different for each spell
        return 20;
    }

    private function damageSpellInitMessage(Living $attacker, Living $victim, Spell $spell): void
    {
        $name = $spell->value;

        $this->act->toChar("You stare at @T and utter the words '$name'!", $attacker, null, $victim);
        $this->act->toVict("@t stares at you and utters the words '$name'!", false, $attacker, null, $victim);
        $this->act->toRoom("@t stares at @T and utters the words '$name'!", false, $attacker, null, $victim, true);
    }

    private function damageSpellHitMessage(Living $attacker, Living $victim, Spell $spell): void
    {
        // TODO: different message for each spell

        $this->act->toChar("Your spell hits @T with devastating effect!", $attacker, null, $victim);
        $this->act->toVict("Spell from @t hits you with devastating effect!", false, $attacker, null, $victim);
        $this->act->toRoom("Spell from @t hits @T with devastating effect!", false, $attacker, null, $victim, true);
    }

    private function damageSpellMissMessage(Living $attacker, Living $victim, Spell $spell): void
    {
        $this->act->toChar("Your spell misses @M!", $attacker, null, $victim);
        $this->act->toVict('Spell from @t misses you!', false, $attacker, null, $victim);
        $this->act->toRoom('Spell from @t misses @T!', false, $attacker, null, $victim, true);
    }
}
