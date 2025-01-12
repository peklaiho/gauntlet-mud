<?php
/**
 * Gauntlet MUD - Unit tests for TelnetHandler
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

use PHPUnit\Framework\TestCase;

use Gauntlet\Descriptor;
use Gauntlet\Network\TelnetHandler;

class TelnetHandlerTest extends TestCase
{
    public function testParseInput()
    {
        $desc = $this->createStub(Descriptor::class);
        $telnet = new TelnetHandler($desc);

        $cmd = chr(TelnetHandler::IAC) .
            chr(TelnetHandler::WILL) .
            chr(TelnetHandler::TELOPT_ECHO);

        $subcmd = chr(TelnetHandler::IAC) .
            chr(TelnetHandler::SB) .
            '12' .
            chr(TelnetHandler::SE) . // Fake end of subnegotiation
            chr(TelnetHandler::IAC) .
            '34' .
            chr(TelnetHandler::IAC) . // Real end of subnegotiation
            chr(TelnetHandler::SE);

        $input = 'ab' . $cmd . 'cd' . $subcmd . 'ef';

        $result = $telnet->parseInput($input);

        $this->assertSame([
            [
                [
                    TelnetHandler::IAC,
                    TelnetHandler::WILL,
                    TelnetHandler::TELOPT_ECHO,
                ],
                [
                    TelnetHandler::IAC,
                    TelnetHandler::SB,
                    ord('1'),
                    ord('2'),
                    TelnetHandler::SE,
                    TelnetHandler::IAC,
                    ord('3'),
                    ord('4'),
                    TelnetHandler::IAC,
                    TelnetHandler::SE,
                ],
            ],
            'abcdef',
        ], $result);
    }
}
