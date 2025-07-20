<?php
/**
 * Gauntlet MUD - Format numbers
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Util;

class NumberFormatter
{
    // Format as float
    public static function format(float|int $number, bool $showPlusSign = false): string
    {
        if (filter_var($number, FILTER_VALIDATE_INT) === false) {
            $result = sprintf('%.1f', $number);
        } else {
            $result = $number;
        }

        if ($showPlusSign && $number > 0) {
            $result = '+' . $result;
        }

        return $result;
    }
}
