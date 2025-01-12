<?php
/**
 * Gauntlet MUD - Format array of strings in a table
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Util;

class TableFormatter
{
    public static function format(array $rows, array $headers, array $leftAlign = []): array
    {
        // Take lengths from headers
        $lengths = array_map('strlen', $headers);

        // Find max length for each column
        foreach ($rows as $row) {
            foreach ($row as $colIndex => $col) {
                $len = strlen($col);
                if ($len > $lengths[$colIndex]) {
                    $lengths[$colIndex] = $len;
                }
            }
        }

        // Build format string
        $format = '';
        foreach ($lengths as $index => $len) {
            $left = in_array($index, $leftAlign);
            $format .= '  %' . ($left ? '-' : '') . $len . 's';
        }

        $output = [];

        // Add headers
        $output[] = sprintf($format, ...$headers);
        $output[] = str_repeat('-', array_sum($lengths) + (2 * count($headers)) + 2);

        // Add rows
        foreach ($rows as $row) {
            $output[] = sprintf($format, ...$row);
        }

        return $output;
    }

    // Format words into variable number of columns based width
    public static function formatWordList(array $data, int $width, int $spaceBetweenWords = 3): array
    {
        // Length of longest word
        $length = max(array_map(fn ($a) => StringLength::length($a), $data));

        // Require space between words
        $length += $spaceBetweenWords;

        // Number of columns and rows
        $columns = intdiv($width, $length);
        $rows = ceil(count($data) / $columns);

        $output = [];

        for ($row = 0; $row < $rows; $row++) {
            $line = '';
            for ($col = 0; $col < $columns; $col++) {
                $txt = $data[($row * $columns) + $col] ?? null;
                if ($txt) {
                    $line .= $txt;

                    if ($col < $columns - 1) {
                        $line .= str_repeat(' ', $length - StringLength::length($txt));
                    }
                }
            }
            $output[] = $line;
        }

        return $output;
    }
}
