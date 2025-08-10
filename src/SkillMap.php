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
use Gauntlet\Util\Config;

class SkillMap
{
    private static ?array $map = null;

    public static function getSkillMapForPlayer(Player $player): array
    {
        // Admins have all skills
        if ($player->getAdminLevel()) {
            $all = [];
            foreach (self::getMap() as $classSkills) {
                $all = array_merge($all, $classSkills);
            }
            usort($all, function ($a, $b) {
                return $a[0] - $b[0];
            });
            return $all;
        }

        // Normal players have skills based on class
        return self::getMap()[$player->getClass()->value];
    }

    public static function getAvailableSkillsForPlayer(Player $player, bool $spellsOnly): array
    {
        $list = self::getSkillMapForPlayer($player);

        $available = [];

        foreach ($list as $skillInfo) {
            if ($spellsOnly && ($skillInfo[1] instanceof Skill)) {
                continue;
            }

            if (!$player->getAdminLevel()) {
                if ($player->getLevel() < $skillInfo[0]) {
                    continue;
                }

                if (Config::useSkillPoints() && $player->getSkillLevel($skillInfo[1]) <= 0) {
                    continue;
                }
            }

            $available[] = $skillInfo[1];
        }

        return $available;
    }

    private static function getMap(): array
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

        return self::$map;
    }
}
