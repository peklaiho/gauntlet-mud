<?php
/**
 * Gauntlet MUD - Fight actions
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

class ActionFight
{
    public function __construct(
        protected Act $act
    ) {

    }

    public function attack(Living $living, Living $target): void
    {
        $this->act->toChar("You attack @T.", $living, null, $target);
        $this->act->toVict('@t attacks you!', false, $living, null, $target);
        $this->act->toRoom('@t attacks @T!', false, $living, null, $target, true);
    }

    public function assist(Living $living, Living $target): void
    {
        $this->act->toChar("You rush to aid @T.", $living, null, $target);
        $this->act->toVict('@t rushes to your aid!', false, $living, null, $target);
        $this->act->toRoom('@t rushes to aid @T!', false, $living, null, $target, true);
    }

    public function backstab(Living $living, Living $target): void
    {
        $weapon = $living->getWeapon();

        if ($weapon) {
            $this->act->toChar("You stab @T in the back with your @i!", $living, $weapon, $target);
            $this->act->toVict('@t stabs you in the back with @s @i!', false, $living, $weapon, $target);
            $this->act->toRoom('@t stabs @T in the back with @s @i!', false, $living, $weapon, $target, true);
        } else {
            $this->act->toChar("You stab @T in the back!", $living, null, $target);
            $this->act->toVict('@t stabs you in the back!', false, $living, null, $target);
            $this->act->toRoom('@t stabs @T in the back!', false, $living, null, $target, true);
        }
    }

    public function disarm(Living $living, Living $target): void
    {
        $weapon = $target->getWeapon();

        if ($weapon) {
            $this->act->toChar("You disarm @T and @E lets go of @S @i!", $living, $weapon, $target);
            $this->act->toVict('@t disarms you and you let go of your @i!', false, $living, $weapon, $target);
            $this->act->toRoom('@t disarms @T and @E lets go of @S @i!', false, $living, $weapon, $target, true);
        }
    }

    public function rescue(Living $living, Living $target): void
    {
        $this->act->toChar("You jump in to rescue @T.", $living, null, $target);
        $this->act->toVict('@t jumps in and rescues you!', false, $living, null, $target);
        $this->act->toRoom('@t jumps in to rescue @T!', false, $living, null, $target, true);
    }
}
