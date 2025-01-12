<?php
/**
 * Gauntlet MUD - RNG functions
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Util;

class Random
{
    /**
     * Return random float between 0 (inclusive) and 1 (exclusive).
     */
    public static function float(): float
    {
        return mt_rand(0, mt_getrandmax() - 1) / mt_getrandmax();
    }

    public static function floatRange(float $min, float $max): float
    {
        // Max value is excluded...
        return $min + (($max - $min) * self::float());
    }

    public static function fromArray(array $arr): mixed
    {
        if (empty($arr)) {
            return null;
        }

        return $arr[self::keyFromArray($arr)];
    }

    public static function keyFromArray(array $arr): mixed
    {
        if (empty($arr)) {
            return null;
        }

        $keys = array_keys($arr);
        $index = self::integer(0, count($keys) - 1);

        return $keys[$index];
    }

    public static function integer(int $min, int $max): int
    {
        if ($min == $max) {
            return $min;
        }

        return mt_rand($min, $max);
    }

    public static function percent(int $percent): bool
    {
        if ($percent <= 0) {
            return false;
        } elseif ($percent >= 100) {
            return true;
        }

        return mt_rand(1, 100) <= $percent;
    }

    public static function permil(int $permil): bool
    {
        if ($permil <= 0) {
            return false;
        } elseif ($permil >= 1000) {
            return true;
        }

        return mt_rand(1, 1000) <= $permil;
    }
}
