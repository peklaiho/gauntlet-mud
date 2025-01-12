<?php
/**
 * Gauntlet MUD - Unit tests for Input
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Gauntlet\Util\Input;
use Gauntlet\Util\StringSplitter;

class InputTest extends TestCase
{
    public static function dataProvider1()
    {
        return [
            ["   command    args   ", false, "   args   "],
            ["   command ", false, ""],
            ["   command", false, ""],
            ["", false, ""],

            // trim
            ["    command     arguments  are  here  ", true, "arguments  are  here"],
        ];
    }

    #[DataProvider('dataProvider1')]
    public function test_getWholeArgument(string $txt, bool $trim, string $expected)
    {
        $splitter = new StringSplitter();
        $input = new Input($txt, $splitter);

        $result = $input->getWholeArgument($trim);

        $this->assertSame($expected, $result);
    }

    public static function dataProvider2()
    {
        return [
            // normal case
            ["   command    aaa  bbb  ccc  ddd  ", 1, false, " bbb  ccc  ddd  "],
            ["   command    aaa  bbb  ccc  ddd  ", 2, false, " ccc  ddd  "],
            ["   command    aaa  bbb  ccc  ddd  ", 3, false, " ddd  "],
            ["   command    aaa  bbb  ccc  ddd  ", 4, false, " "],
            ["   command    aaa  bbb  ccc  ddd  ", 5, false, ""],

            // trim
            ["   command    aaa  bbb  ccc  ddd ", 1, true, "bbb  ccc  ddd"],
            ["   command    aaa  bbb  ccc  ddd ", 2, true, "ccc  ddd"],
            ["   command    aaa  bbb  ccc  ddd ", 3, true, "ddd"],
            ["   command    aaa  bbb  ccc  ddd ", 4, true, ""],
            ["   command    aaa  bbb  ccc  ddd ", 5, true, ""],
        ];
    }

    #[DataProvider('dataProvider2')]
    public function test_getWholeArgSkip(string $txt, int $skip, bool $trim, string $expected)
    {
        $splitter = new StringSplitter();
        $input = new Input($txt, $splitter);

        $result = $input->getWholeArgSkip($skip, $trim);

        $this->assertSame($expected, $result);
    }

    public static function dataProvider3()
    {
        return [
            ["   command   arg1  arg2  arg3  ", "command", ["arg1", "arg2", "arg3"]],
            ["   command   ", "command", []],
            ["   ", "", []],
        ];
    }

    #[DataProvider('dataProvider3')]
    public function test_other_methods(string $txt, string $cmd, array $args)
    {
        $splitter = new StringSplitter();
        $input = new Input($txt, $splitter);

        // Count
        $this->assertSame(count($args), $input->count());

        // Access
        for ($i = 0; $i < count($args); $i++) {
            $this->assertSame($args[$i], $input->get($i));
        }

        // Other
        $this->assertSame($txt, $input->getRaw());
        $this->assertSame(trim($txt), $input->getRaw(true));
        $this->assertSame($cmd, $input->getCommand());
    }
}
