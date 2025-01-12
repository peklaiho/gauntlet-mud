<?php
/**
 * Gauntlet MUD - Helper function to calculate string length
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Util;

/**
 * Calculate string length by ignoring escape sequences.
 */
class StringLength
{
    public static function length(string $txt): int
    {
        $len = 0;

        // Characters between these values are ignored
        $start = ["\033"];
        $end = ["m"];

        $ignore = false;

        for ($i = 0; $i < strlen($txt); $i++) {
            $c = substr($txt, $i, 1);

            if ($ignore) {
                if (in_array($c, $end)) {
                    $ignore = false;
                }
            } else {
                if (in_array($c, $start)) {
                    $ignore = true;
                } else {
                    $len++;
                }
            }
        }

        return $len;
    }
}
