<?php
/**
 * Gauntlet MUD - Unit tests for StringLength
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Gauntlet\Util\StringLength;

class StringLengthTest extends TestCase
{
    public static function textProvider()
    {
        return [
            ["aaa bbb ccc", 11],
            ["aaa \033[1;31mbbb\033[0m \033[1;35mccc\033[0m", 11],
        ];
    }

    #[DataProvider('textProvider')]
    public function test_length($input, $expected)
    {
        $result = StringLength::length($input);

        $this->assertSame($expected, $result);
    }
}
