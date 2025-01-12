<?php
/**
 * Gauntlet MUD - Helper functions for splitting strings
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Util;

class StringSplitter
{
    public static function paragraph(string $input, int $lineWidth): array
    {
        $words = self::splitBySpace($input);

        $lines = [];
        $line = '';

        foreach ($words as $word) {
            if (StringLength::length($line) + StringLength::length($word) + 1 > $lineWidth) {
                if ($line) {
                    $lines[] = $line;
                }
                $line = $word;
            } else {
                if ($line) {
                    $line .= ' ';
                }
                $line .= $word;
            }
        }
        $lines[] = $line;

        return $lines;
    }

    public static function sentences(string $input): array
    {
        $words = self::splitBySpace($input);

        $lines = [];
        $line = '';

        foreach ($words as $word) {
            if ($line) {
                $line .= ' ';
            }

            $line .= $word;

            if (str_ends_with($word, '.') ||
                str_ends_with($word, '!') ||
                str_ends_with($word, '?')) {
                $lines[] = $line;
                $line = '';
            }
        }

        if ($line) {
            $lines[] = $line;
        }

        return $lines;
    }

    public static function splitInput(string $input): ?array
    {
        $split = preg_split('/[\n\r]+/', $input, 2);

        if (count($split) == 2) {
            return $split;
        }

        return null;
    }

    public static function splitByNewline(string $input): array
    {
        return preg_split('/\r?\n/', $input);
    }

    public static function splitBySpace(string $input): array
    {
        return preg_split('/\s+/', $input, -1, PREG_SPLIT_NO_EMPTY);
    }
}
