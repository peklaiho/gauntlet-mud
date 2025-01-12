<?php
/**
 * Gauntlet MUD - Helper functions for working with bytes
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Util;

class Bytes
{
    public static function bytesToString(int $b): string
    {
        $gig = 1024 * 1024 * 1024;
        $meg = 1024 * 1024;
        $kil = 1024;

        if ($b >= $gig) {
            return round($b / $gig, 2) . 'G';
        } elseif ($b >= $meg) {
            return round($b / $meg, 2) . 'M';
        } elseif ($b >= $kil) {
            return round($b / $kil, 2) . 'k';
        }

        return $b;
    }
}
