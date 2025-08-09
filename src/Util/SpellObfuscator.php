<?php
/**
 * Gauntlet MUD - Obfuscator for spell names
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Util;

class SpellObfuscator
{
    private static array $vowels = ['a', 'e', 'i', 'o', 'u'];
    private static array $consonants = [
        'b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm',
        'n', 'p', 'q', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z'
    ];
    private static int $shift = 3;

    public static function obfuscate(string $name): string
    {
        $result = '';

        foreach (str_split($name) as $char) {
            $upperCase = ctype_upper($char);
            $char = strtolower($char);

            $index = array_search($char, self::$vowels);
            if ($index !== false) {
                $char = self::$vowels[self::newIndex($index, count(self::$vowels))];
            } else {
                $index = array_search($char, self::$consonants);
                if ($index !== false) {
                    $char = self::$consonants[self::newIndex($index, count(self::$consonants))];
                }
            }

            if ($upperCase) {
                $char = strtoupper($char);
            }

            $result .= $char;
        }

        return $result;
    }

    private static function newIndex(int $index, int $count): int
    {
        $index += self::$shift;

        if ($index >= $count) {
            $index -= $count;
        }

        return $index;
    }
}
