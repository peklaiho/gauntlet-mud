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
        static $table = [
            15,
            25,
            35,
            50,
            65,
            80,
            95,
            110,
            125,
            145,
            165,
            185,
            205,
            225,
            245,
            265,
            285,
            305,
            325,
            355,
            385,
            415,
            445,
            475,
            505,
            535,
            565,
            595,
            625,
            665,
            705,
            745,
            785,
            825,
            865,
            905,
            945,
            985,
            1025,
            1075,
            1125,
            1175,
            1225,
            1275,
            1335,
            1395,
            1465,
            1535,
            1615,
            1705,
        ];

        return $table[$level - 1];
    }

    public static function getDamageMultiplier(int $level): float
    {
        // 1.0 at level 1
        // 5.08 at level 50

        return ($level + 11) / 12;
    }

    public static function getDamageBonus(int $level): float
    {
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
