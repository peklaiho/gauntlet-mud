<?php
/**
 * Gauntlet MUD - Helper functions for validating strings
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Util;

class StringValidator
{
    const LOWER_CHARS = 'abcdefghijklmnopqrstuvwxyz';
    const UPPER_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const NUMBERS = '0123456789';

    public static function validPlayerName(string $val): bool
    {
        return preg_match('/^[A-Z][a-z]{2,15}$/', $val) === 1;
    }

    public static function validPassword(string $val): bool
    {
        // Accept any characters, just check length
        return strlen($val) >= 5 && strlen($val) <= 64;
    }

    public static function validLettersAndPunctuation(string $val): bool
    {
        // Limited special characters allowed
        static $allowed = self::NUMBERS .
            self::LOWER_CHARS . self::UPPER_CHARS .
            " ,.!?-:;/\"'\n";

        return strlen($val) == strspn($val, $allowed);
    }

    public static function validPrintableAscii(string $val): bool
    {
        // All printable ASCII characters and newline
        $allowed = implode(array_map('chr', range(32, 126))) . "\n";

        return strlen($val) == strspn($val, $allowed);
    }
}
