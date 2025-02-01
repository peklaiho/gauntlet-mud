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
    protected static ?array $table = null;

    public static function getExpTable(): array
    {
        // Create exp table
        if (!self::$table) {
            $monstersToKill = function ($level) {
                return 8 * pow(1.09, $level);
            };

            self::$table = [0];

            for ($level = 1; $level < MAX_LEVEL; $level++) {
                self::$table[] = intval(round($monstersToKill($level) * MonsterStats::getExperience($level)));
            }
        }

        return self::$table;
    }

    public static function getPlayerExpToLevel(int $level): int
    {
        $table = self::getExpTable();

        $exp = 0;

        for ($i = 0; $i < $level; $i++) {
            $exp += $table[$i];
        }

        return $exp;
    }

    public static function getPlayerLevelByExp(int $exp): int
    {
        for ($i = MAX_LEVEL; $i >= 1; $i--) {
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

        return max(0, 1 - (($playerLevel - $monsterLevel) * 0.1));
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
