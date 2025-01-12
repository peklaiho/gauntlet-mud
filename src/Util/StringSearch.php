<?php
/**
 * Gauntlet MUD - Helper functions for searching strings
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Util;

class StringSearch
{
    public static function search(array $input, string $search, array $options = []): array
    {
        // Read options
        $searchFn = 'strpos';
        if ($options['ignore_case'] ?? false) {
            $searchFn = 'stripos';
        }

        $excLen = $options['exc_len'] ?? 60;
        $excPrependLen = intval(floor(($excLen - strlen($search)) / 2));
        $excAppendLen = intval(ceil(($excLen - strlen($search)) / 2));

        $results = [];

        foreach ($input as $key => $val) {
            $start = $searchFn($val, $search);

            if ($start === false) {
                continue;
            }

            $exc = '{' . $search . '}';

            // Prepend characters to excerpt
            $i = $start - 1;
            $c = 0;
            while ($i >= 0 && $val[$i] != "\n" && $c < $excPrependLen) {
                $exc = $val[$i] . $exc;
                $i--;
                $c++;
            }

            // Append characters to excerpt
            $i = $start + strlen($search);
            $c = 0;
            while ($i < strlen($val) && $val[$i] != "\n" && $c < $excAppendLen) {
                $exc .= $val[$i];
                $i++;
                $c++;
            }

            $results[] = [
                'key' => $key,
                'pos' => $start,
                'exc' => $exc,
            ];
        }

        return $results;
    }
}
