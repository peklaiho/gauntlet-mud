<?php
/**
 * Gauntlet MUD - Map of skills
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Enum\PlayerClass;
use Gauntlet\Enum\Skill;

class SkillMap
{
    private static ?array $map = null;

    public static function getSkills(PlayerClass $class): array
    {
        if (!self::$map) {
            self::$map = [
                PlayerClass::Mage->value => [

                ],

                PlayerClass::Rogue->value => [
                    [8, Skill::Backstab],
                ],

                PlayerClass::Shaman->value => [

                ],

                PlayerClass::Warrior->value => [
                    [8, Skill::Rescue],
                ],
            ];
        }

        return self::$map[$class->value];
    }
}
