<?php
/**
 * Gauntlet MUD - Format functions for times
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Util;

class TimeFormatter
{
    public static function getParts(int $time): array
    {
        $days = intdiv($time, 86400);
        if ($days > 0) {
            $time -= $days * 86400;
        }

        $hours = intdiv($time, 3600);
        if ($hours > 0) {
            $time -= $hours * 3600;
        }

        $mins = intdiv($time, 60);
        if ($mins > 0) {
            $time -= $mins * 60;
        }

        return [$days, $hours, $mins, $time];
    }

    public static function timeToString(int $time): string
    {
        $parts = self::getParts($time);
        $types = ['day', 'hour', 'minute', 'second'];
        $result = [];

        for ($i = 0; $i < 4; $i++) {
            if ($parts[$i] > 0) {
                $result[] = $parts[$i] . ' ' . $types[$i] . ($parts[$i] > 1 ? 's' : '');
            }
        }

        return implode(', ', $result);
    }

    public static function timeToShortString(int $time, bool $separateBySpace = false): string
    {
        $parts = self::getParts($time);
        $types = ['d', 'h', 'm', 's'];
        $result = '';

        for ($i = 0; $i < 4; $i++) {
            if ($parts[$i] > 0) {
                if ($separateBySpace && $result) {
                    $result .= ' ';
                }
                $result .= $parts[$i] . $types[$i];
            }
        }

        return $result;
    }
}
