<?php
/**
 * Gauntlet MUD - Helper functions for currencies
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Util;

use Gauntlet\Enum\MoneyType;

/**
 * Helper functions for handling different coins: gold, silver and copper.
 * 1 gold equals 100 silver
 * 1 silver equals 100 copper
 */
class Currency
{
    public static function splitCoins(int $coins): array
    {
        $gold = intdiv($coins, 10000);
        $remainder = $coins % 10000;
        $silver = intdiv($remainder, 100);
        $copper = $remainder % 100;

        return [
            $gold,
            $silver,
            $copper
        ];
    }

    public static function format(int $coins, bool $short): ?string
    {
        if (Config::moneyType() == MoneyType::Credits) {
            return self::formatCredits($coins);
        }

        return self::formatCoins($coins, $short);
    }

    public static function formatCoins(int $coins, bool $short): ?string
    {
        if ($coins == 0) {
            return null;
        }

        $data = self::splitCoins($coins);
        $parts = [];

        if ($data[0] > 0) {
            $parts[] = $data[0] . ($short ? 'g' : ' gold');
        }
        if ($data[1] > 0) {
            $parts[] = $data[1] . ($short ? 's' : ' silver');
        }
        if ($data[2] > 0) {
            $parts[] = $data[2] . ($short ? 'c' : ' copper');
        }

        return match(count($parts)) {
            1 => $parts[0],
            2 => $parts[0] . ($short ? ' ' : ' and ') . $parts[1],
            3 => $parts[0] . ($short ? ' ' : ', ') . $parts[1] . ($short ? ' ' : ' and ') . $parts[2]
        };
    }

    public static function formatCredits(int $coins): ?string
    {
        if ($coins == 0) {
            return null;
        }

        return number_format($coins, 0, '.', ',');
    }

    public static function parse(string $str): ?int
    {
        if (Config::moneyType() == MoneyType::Credits) {
            return self::parseCredits($str);
        }

        return self::parseCoins($str);
    }

    public static function parseCoins(string $str): ?int
    {
        preg_match_all('/[0-9]+[gsc]/', $str, $matches);
        $matches = $matches[0];

        // Not a valid currency string
        if (empty($matches)) {
            return null;
        }

        $coins = 0;

        foreach ($matches as $m) {
            $amount = intval(substr($m, 0, -1));
            $type = substr($m, -1);

            if ($type == 'g') {
                $amount *= 10000;
            } elseif ($type == 's') {
                $amount *= 100;
            }

            $coins += $amount;
        }

        return $coins;
    }

    public static function parseCredits(string $str): ?int
    {
        // Trim and remove commas (thousand separators)
        $str = trim(str_replace(',', '', $str));

        if (preg_match('/^[0-9]+$/', $str)) {
            return intval($str);
        }

        return null;
    }
}
