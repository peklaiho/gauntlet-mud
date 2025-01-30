<?php
/**
 * Gauntlet MUD - Calculate monster stats based on level
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

class MonsterStats
{
    public static function getBaseHealth(int $level): float
    {
        // 16 at level 1
        // 114 at level 10
        // 253 at level 20
        // 447 at level 30
        // 773 at level 40
        // 1435 at level 50

        $h1 = 15 * pow(1.1, $level);
        $h2 = 15 * pow($level, 1.1);

        return ($h1 + $h2) / 2;
    }

    public static function getDamageMultiplier(int $level): float
    {
        // 1.0 at level 1
        // 5.08 at level 50

        return ($level + 11) / 12;
    }

    public static function getDamageBonus(int $level): float
    {
        // 0.4 at level 1
        // 20 at level 50

        return $level / 2.5;
    }

    public static function getExpMultiplier(int $level): float
    {
        // 1.0 at level 1
        // 2.0 at level 50

        return ($level + 48) / 49;
    }

    public static function getExperience(int $level): int
    {
        return intval(self::getBaseHealth($level) * self::getExpMultiplier($level));
    }
}
