<?php
/**
 * Gauntlet MUD - Unit tests for StringSearch
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Gauntlet\Util\StringSearch;

class StringSearchTest extends TestCase
{
    public static function searchProvider(): array
    {
        return [
            // Basic case
            [
                [
                    'abc123' => 'Lets write some text here and see how it search works.',
                ],
                'here',
                [
                    'exc_len' => 10
                ],
                [
                    [
                        'key' => 'abc123',
                        'pos' => 21,
                        'exc' => 'xt {here} an',
                    ]
                ]
            ],

            // Uneven excerpt length
            [
                [
                    'abc123' => 'Lets write some text here and see how it search works.',
                ],
                'here',
                [
                    'exc_len' => 15
                ],
                [
                    [
                        'key' => 'abc123',
                        'pos' => 21,
                        'exc' => 'text {here} and s',
                    ]
                ]
            ],

            // Very long excerpt length
            [
                [
                    'abc123' => 'Lets write some text here and see how it search works.',
                ],
                'here',
                [
                    'exc_len' => 1000
                ],
                [
                    [
                        'key' => 'abc123',
                        'pos' => 21,
                        'exc' => 'Lets write some text {here} and see how it search works.',
                    ]
                ]
            ],

            // Excerpt stops at newline characters
            [
                [
                    'abc123' => "Lets write\n some text here and see how \nit search works.",
                ],
                'here',
                [
                    'exc_len' => 1000
                ],
                [
                    [
                        'key' => 'abc123',
                        'pos' => 22,
                        'exc' => ' some text {here} and see how ',
                    ]
                ]
            ],
        ];
    }

    #[DataProvider('searchProvider')]
    public function test_search(array $input, string $search, array $options, array $expected)
    {
        $result = StringSearch::search($input, $search, $options, $expected);

        $this->assertSame($expected, $result);
    }
}
