<?php
/**
 * Gauntlet MUD - Find closest candidate from lists of strings based on Levenshtein distance
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Util;

class Levenshtein
{
    public static function findClosest(string $word, array $candidates, int $maxDistance): ?string
    {
        $minDistance = 1000;
        $minIndex = -1;

        foreach ($candidates as $index => $cand) {
            $l = levenshtein(strtolower($word), strtolower($cand));

            if ($l <= $maxDistance && $l < $minDistance) {
                $minDistance = $l;
                $minIndex = $index;
            }
        }

        return $minIndex < 0 ? null : $candidates[$minIndex];
    }
}
