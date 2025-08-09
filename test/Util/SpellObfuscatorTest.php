<?php
/**
 * Gauntlet MUD - Unit tests for SpellObfuscator
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Gauntlet\Util\SpellObfuscator;

class SpellObfuscatorTest extends TestCase
{
    public static function dataProvider1()
    {
        return [
            ['Alpha and Omega', 'Opslo orh Equko'],
            ['Masters of the Universe', 'Qowxuvw ej xlu Irayuvwu'],
        ];
    }

    #[DataProvider('dataProvider1')]
    public function test_obfuscate(string $txt, string $expected)
    {
        $result = SpellObfuscator::obfuscate($txt);
        $this->assertSame($expected, $result);
    }
}
