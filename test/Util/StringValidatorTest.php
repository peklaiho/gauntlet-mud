<?php
/**
 * Gauntlet MUD - Unit tests for StringValidator
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Gauntlet\Util\StringValidator;

class StringValidatorTest extends TestCase
{
    public static function dataProvider1()
    {
        return [
            [" bob", false],
            ["bob", false],
            ["Bo_b", false],
            ["BobBy", false],

            ["Bob", true],
            ["Bobby", true],

            // long names
            ["Longnamelongname", true],
            ["Longnamelongnames", false],
        ];
    }

    #[DataProvider('dataProvider1')]
    public function test_validPlayerName(string $txt, bool $expected)
    {
        $result = StringValidator::validPlayerName($txt);

        $this->assertSame($expected, $result);
    }

    public static function dataProvider2()
    {
        return [
            // Min length
            ["abcd", false],
            ["abcde", true],

            // Max length
            ["aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa", false],
            ["aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa", true],

            // Special chars
            [" -,:;/\"'@#$%^&*=+_()[]{}<>", true],
        ];
    }

    #[DataProvider('dataProvider2')]
    public function test_validPassword(string $txt, bool $expected)
    {
        $result = StringValidator::validPassword($txt);

        $this->assertSame($expected, $result);
    }

    public static function dataProvider3()
    {
        return [
            // Allowed special characters
            [' ', true],
            ['-', true],
            [',', true],
            [':', true],
            [';', true],
            ['/', true],
            ['"', true],
            ["'", true],

            // Forbidden special characters
            ['@', false],
            ['#', false],
            ['$', false],
            ['%', false],
            ['^', false],
            ['&', false],
            ['*', false],
            ['+', false],
            ['_', false],
            ['(', false],
            [')', false],
            ['[', false],
            [']', false],
            ['{', false],
            ['}', false],
            ['<', false],
            ['>', false],

            ["This is a normal sentence. And another! And a question?", true],
            ["Other allowed characters include -,:;/ and quotes such as \" and '.", true],
            ["Lets make sure numbers 0123456789 are allowed as well.", true],
        ];
    }

    #[DataProvider('dataProvider3')]
    public function test_validLettersAndPunctuation(string $txt, bool $expected)
    {
        $result = StringValidator::validLettersAndPunctuation($txt);

        $this->assertSame($expected, $result);
    }
}
