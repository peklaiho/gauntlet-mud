<?php
/**
 * Gauntlet MUD - Color functions
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Util;

use Gauntlet\Player;

class Color
{
    const BLACK    = 'k';
    const RED      = 'r';
    const GREEN    = 'g';
    const YELLOW   = 'y';
    const BLUE     = 'b';
    const MAGENTA  = 'm';
    const CYAN     = 'c';
    const WHITE    = 'w';

    const BBLACK   = 'K';
    const BRED     = 'R';
    const BGREEN   = 'G';
    const BYELLOW  = 'Y';
    const BBLUE    = 'B';
    const BMAGENTA = 'M';
    const BCYAN    = 'C';
    const BWHITE   = 'W';

    const RESET    = 'n';

    private static array $codes = [
        self::BLACK    => "\033[0;30m",
        self::RED      => "\033[0;31m",
        self::GREEN    => "\033[0;32m",
        self::YELLOW   => "\033[0;33m",
        self::BLUE     => "\033[0;34m",
        self::MAGENTA  => "\033[0;35m",
        self::CYAN     => "\033[0;36m",
        self::WHITE    => "\033[0;37m",

        self::BBLACK   => "\033[1;30m",
        self::BRED     => "\033[1;31m",
        self::BGREEN   => "\033[1;32m",
        self::BYELLOW  => "\033[1;33m",
        self::BBLUE    => "\033[1;34m",
        self::BMAGENTA => "\033[1;35m",
        self::BCYAN    => "\033[1;36m",
        self::BWHITE   => "\033[1;37m",

        self::RESET    => "\033[0m"
    ];

    public static function getCode(string $color): string
    {
        return self::$codes[$color] ?? '';
    }

    public static function color256(int $color): string
    {
        if ($color < 0 || $color > 255) {
            return '';
        }

        return "\033[38;5;{$color}m";
    }

    public static function isValid(string $col): bool
    {
        return array_key_exists($col, self::$codes);
    }
}
