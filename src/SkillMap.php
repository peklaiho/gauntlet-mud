<?php
/**
 * Gauntlet MUD - Map of skills
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Enum\PlayerClass;
use Gauntlet\Enum\Spell;
use Gauntlet\Enum\Skill;

class SkillMap
{
    private static ?array $map = null;

    public static function getSkills(PlayerClass $class): array
    {
        if (!self::$map) {
            self::$map = [
                PlayerClass::Cleric->value => [
                    [3, Spell::MinorProtection]
                ],

                PlayerClass::Mage->value => [

                ],

                PlayerClass::Rogue->value => [
                    [5, Skill::Backstab],
                ],

                PlayerClass::Warrior->value => [
                    [5, Skill::Rescue],
                    [15, Skill::Disarm],
                    [20, Skill::SecondAttack],
                    [40, Skill::ThirdAttack],
                ],
            ];
        }

        return self::$map[$class->value];
    }
}
