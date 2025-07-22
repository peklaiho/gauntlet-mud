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

    public static function getSkillsForClass(PlayerClass $class): array
    {
        if (!self::$map) {
            self::$map = [
                PlayerClass::Cleric->value => [
                    [3, Spell::MinorProtection],
                    [15, Spell::MajorProtection],
                ],

                PlayerClass::Mage->value => [
                    [3, Spell::MagicMissile],
                    [8, Spell::FireBolt],
                    [12, Spell::ChillBones],
                    [20, Spell::FireBall],
                    [45, Spell::AlphaAndOmega],
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

    public static function getSkillsForPlayer(Player $player): array
    {
        $list = self::getSkillsForClass($player->getClass());

        $available = [];

        foreach ($list as $skillInfo) {
            if ($player->getAdminLevel() || $player->getLevel() >= $skillInfo[0]) {
                $available[] = $skillInfo[1];
            }
        }

        return $available;
    }
}
