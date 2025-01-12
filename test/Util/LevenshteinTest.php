<?php
/**
 * Gauntlet MUD - Unit tests for Levenshtein
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Gauntlet\Util\Levenshtein;

class LevenshteinTest extends TestCase
{
    public static function dataProvider1()
    {
        return [
            ["loookk", ['north', 'get', 'pick', 'fight', 'look', 'commands', 'who'], "look"],
        ];
    }

    #[DataProvider('dataProvider1')]
    public function test_findClosest(string $word, array $candidates, string $expected)
    {
        $result = Levenshtein::findClosest($word, $candidates, 2);

        $this->assertSame($expected, $result);
    }
}
