<?php
/**
 * Gauntlet MUD - Unit tests for MarkdownConverter
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Gauntlet\Util\MarkdownConverter;

class MarkdownConverterTest extends TestCase
{
    public static function dataProvider1()
    {
        return [
            // Headings
            ["# First level header, ## Second level header, ### Third level header", "First level header, Second level header, Third level header"],

            // Emphasis
            ["Some text with **bold** and *italic* contents", "Some text with bold and italic contents"],

            // Remote links
            ["Some text with remote [example](http://example.com/) links", "Some text with remote example: http://example.com/ links"],

            // Local links
            ["Some text with local [name](target) links", "Some text with local {name} links"],

            // Lists stay unchanged
            ["* List item 1 * List item 2 * List item 3", "* List item 1 * List item 2 * List item 3"],
        ];
    }

    #[DataProvider('dataProvider1')]
    public function test_convert(string $input, string $expected)
    {
        $result = MarkdownConverter::convert($input);

        $this->assertSame($expected, $result);
    }
}
