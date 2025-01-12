<?php
/**
 * Gauntlet MUD - Functions related to experience
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Util\Log;

class Experience
{
    public static function getPlayerExpToLevel(int $level): int
    {
        static $table = [
            0,
            105,
            335,
            772,
            1440,
            2370,
            3595,
            5151,
            7076,
            9414,
            12312,
            15835,
            20052,
            25040,
            30883,
            37672,
            45504,
            54486,
            64735,
            76375,
            89925,
            105582,
            123559,
            144089,
            167423,
            193833,
            223613,
            257082,
            294583,
            338485,
            390535,
            452012,
            524378,
            609303,
            708692,
            824720,
            959861,
            1116934,
            1299147,
            1510146,
            1756365,
            2043023,
            2376063,
            2762239,
            3209230,
            3729654,
            4334282,
            5040176,
            5862326,
            6823735,
        ];

        return $table[$level - 1];
    }

    public static function getPlayerLevelByExp(int $exp): int
    {
        for ($i = 50; $i >= 1; $i--) {
            if ($exp >= self::getPlayerExpToLevel($i)) {
                return $i;
            }
        }
    }

    public static function getPenaltyMultiplier(int $playerLevel, int $monsterLevel): float
    {
        if ($playerLevel <= $monsterLevel) {
            return 1;
        }

        return max(0.1, 1 - (($playerLevel - $monsterLevel) * 0.05));
    }

    public static function getExpGain(Player $player, Monster $monster, float $damage): int
    {
        $monsterExp = $monster->getExperience();
        $portion = $damage / $monster->getMaxHealth();
        $penalty = self::getPenaltyMultiplier($player->getLevel(), $monster->getLevel());

        return intval(round($monsterExp * $portion * $penalty));
    }

    public static function gainExperienceFromVictim(Player $player, Living $victim, float $damage): bool
    {
        // No exp gain from players from now
        if ($victim->isPlayer()) {
            return false;
        }

        $amount = self::getExpGain($player, $victim, $damage);

        return self::gainExperience($player, $amount);
    }

    public static function gainExperience(Player $player, int $amount): bool
    {
        $player->addExperience($amount);

        $oldLevel = $player->getLevel();
        $newLevel = self::getPlayerLevelByExp($player->getExperience());

        if ($newLevel > $oldLevel) {
            $player->outln('You have gained a level!');
            $player->setLevel($newLevel);
            Log::info($player->getName() . " advanced to level $newLevel.");
            return true;
        } elseif ($newLevel < $oldLevel) {
            $player->outln('You have lost a level!');
            $player->setLevel($newLevel);
            Log::info($player->getName() . " was demoted to level $newLevel.");
            return true;
        }

        return false;
    }
}
