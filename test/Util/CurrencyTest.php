<?php
/**
 * Gauntlet MUD - Unit tests for Currency
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Gauntlet\Util\Currency;

class CurrencyTest extends TestCase
{
    public static function splitCoinsProvider()
    {
        return [
            [0, [0, 0, 0]],
            [1, [0, 0, 1]],
            [100, [0, 1, 0]],
            [10000, [1, 0, 0]],
            [20304, [2, 3, 4]],
        ];
    }

    #[DataProvider('splitCoinsProvider')]
    public function test_splitCoins($coins, $expected)
    {
        $result = Currency::splitCoins($coins);

        $this->assertSame($expected, $result);
    }

    public static function formatCoinsProvider()
    {
        return [
            [0, true, null],

            // Short formats
            [1, true, '1c'],
            [100, true, '1s'],
            [10000, true, '1g'],
            [203, true, '2s 3c'],
            [30400, true, '3g 4s'],
            [40005, true, '4g 5c'],
            [70809, true, '7g 8s 9c'],
            [708090, true, '70g 80s 90c'],

            // Long formats
            [1, false, '1 copper'],
            [100, false, '1 silver'],
            [10000, false, '1 gold'],
            [203, false, '2 silver and 3 copper'],
            [30400, false, '3 gold and 4 silver'],
            [40005, false, '4 gold and 5 copper'],
            [70809, false, '7 gold, 8 silver and 9 copper'],
            [708090, false, '70 gold, 80 silver and 90 copper'],
        ];
    }

    #[DataProvider('formatCoinsProvider')]
    public function test_formatCoins($coins, $short, $expected)
    {
        $result = Currency::formatCoins($coins, $short);

        $this->assertSame($expected, $result);
    }

    public static function formatCreditsProvider()
    {
        return [
            [0, null],
            [1, '1'],
            [123, '123'],
            [1234, '1,234'],
            [123456789, '123,456,789'],
        ];
    }

    #[DataProvider('formatCreditsProvider')]
    public function test_formatCredits($coins, $expected)
    {
        $result = Currency::formatCredits($coins);

        $this->assertSame($expected, $result);
    }

    public static function parseCoinsProvider()
    {
        return [
            ['abc', null],

            ['1c', 1],
            ['2s', 200],
            ['3g', 30000],

            ['2s 3c', 203],
            ['3g 4c', 30004],
            ['4g 5s', 40500],

            ['2g3s4c', 20304],
            ['3g 4s 5c', 30405],
            ['41g, 52s, 63c', 415263],
        ];
    }

    #[DataProvider('parseCoinsProvider')]
    public function test_parseCoins($str, $expected)
    {
        $result = Currency::parseCoins($str);

        $this->assertSame($expected, $result);
    }

    public static function parseCreditsProvider()
    {
        return [
            ['abc', null],
            [' 1,234 ', 1234],
            [' 123,456,789 ', 123456789],
        ];
    }

    #[DataProvider('parseCreditsProvider')]
    public function test_parseCredits($str, $expected)
    {
        $result = Currency::parseCredits($str);

        $this->assertSame($expected, $result);
    }
}
