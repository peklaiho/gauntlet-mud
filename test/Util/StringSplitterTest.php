<?php
/**
 * Gauntlet MUD - Unit tests for StringSplitter
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Gauntlet\Util\StringSplitter;

class StringSplitterTest extends TestCase
{
    public static function paragraphProvider(): array
    {
        return [
            [
                'Lets write some sentences here and see how it splits them ' .
                'into multiple lines. It should work nicely.',
                32,
                [
                    'Lets write some sentences here',
                    'and see how it splits them into',
                    'multiple lines. It should work',
                    'nicely.',
                ]
            ]
        ];
    }

    #[DataProvider('paragraphProvider')]
    public function test_paragraph(string $input, int $lineWidth, array $expected)
    {
        $result = StringSplitter::paragraph($input, $lineWidth);

        $this->assertSame($expected, $result);
    }

    public static function sentencesProvider(): array
    {
        return [
            [
                'Here is one sentence. And here is another! Lets test if ' .
                'this works correctly? Overflow without punctuation',
                [
                    'Here is one sentence.',
                    'And here is another!',
                    'Lets test if this works correctly?',
                    'Overflow without punctuation',
                ]
            ]
        ];
    }

    #[DataProvider('sentencesProvider')]
    public function test_sentences(string $input, array $expected)
    {
        $result = StringSplitter::sentences($input);

        $this->assertSame($expected, $result);
    }

    public static function inputProvider(): array
    {
        return [
            [" aaa bbb \r\n ccc \r\n ddd ", [" aaa bbb ", " ccc \r\n ddd "]],
            ["\n", ["", ""]],
            [" input without linebreak ", null],
        ];
    }

    #[DataProvider('inputProvider')]
    public function test_splitInput(string $input, ?array $expected)
    {
        $result = StringSplitter::splitInput($input);

        $this->assertSame($expected, $result);
    }

    public static function spaceProvider(): array
    {
        return [
            ["  aaa   bbb   ccc   ", ["aaa", "bbb", "ccc"]],
            [" ", []],
            ["", []],
        ];
    }

    #[DataProvider('spaceProvider')]
    public function test_splitBySpace(string $input, array $expected)
    {
        $result = StringSplitter::splitBySpace($input);

        $this->assertSame($expected, $result);
    }
}
