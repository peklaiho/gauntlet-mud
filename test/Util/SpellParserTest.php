<?php
/**
 * Gauntlet MUD - Unit tests for SpellParser
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Gauntlet\Enum\Spell;
use Gauntlet\Util\Input;
use Gauntlet\Util\SpellParser;
use Gauntlet\Util\StringSplitter;

class SpellParserTest extends TestCase
{
    public static function dataProvider1()
    {
        return [
            ['', null],
            ['unknown spell', null],

            ['alpha and omega', [Spell::AlphaAndOmega, null]],
            ['alpha and omega target', [Spell::AlphaAndOmega, 'target']],
            ['alpha and omeba', [Spell::AlphaAndOmega, 'omeba']],

            // Capitalized
            ['Alpha And omeGA', [Spell::AlphaAndOmega, null]],
            ['Alpha And omeGA target', [Spell::AlphaAndOmega, 'target']],
            ['Alpha And omeBA', [Spell::AlphaAndOmega, 'omeBA']],

            // Abbreviated
            ['alp an om', [Spell::AlphaAndOmega, null]],
            ['alp an om target', [Spell::AlphaAndOmega, 'target']],
            ['alp an omeba', [Spell::AlphaAndOmega, 'omeba']],
        ];
    }

    #[DataProvider('dataProvider1')]
    public function test_parse(string $txt, ?array $expected)
    {
        $splitter = new StringSplitter();
        $input = new Input('cast ' . $txt, $splitter);

        $result = SpellParser::parse($input, [Spell::AlphaAndOmega]);

        $this->assertSame($expected, $result);
    }
}
