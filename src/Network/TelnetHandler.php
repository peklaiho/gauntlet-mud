<?php
/**
 * Gauntlet MUD - Telnet protocol handler
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Network;

use Gauntlet\Descriptor;

// Class for handling Telnet negotiation and MUD protocols
//
// https://tintin.mudhalla.net/protocols/
// https://github.com/scandum/mth
//
// Sender  Receiver  Meaning
// -------------------------
// WILL    DO        Sender wants to enable an option with TELNET WILL
//                   command, and the receiver agrees to that by sending
//                   back a TELNET DO command.
// WILL    DONT      Sender wants to enable an option with TELNET WILL
//                   command, and the receiver does not agrees to that
//                   by sending back a TELNET DONT command.
// DO      WILL      Sender wants the receiver to enable an option with
//                   TELNET DO command, and the receiver agrees to that
//                   by sending back a TELNET WILL command.
// DO      WONT      Sender wants the receiver to enable an option with
//                   TELNET DO command, and the receiver does not agrees
//                   to that by sending back a TELNET WONT command.
// WONT    DONT      The sender wants not to use and option with TELNET
//                   WONT command, and the receiver confirms to that by
//                   sending back a DONT command.
// DONT    WONT      The sender wants the receiver not to use and option
//                   with TELNET DON’T command, and the receiver confirms
//                   to that by sending back a WON’T command.
class TelnetHandler
{
    const IAC   = 255; // Interpret as command
    const DONT  = 254; // You are not to use option
    const DO    = 253; // You use option
    const WONT  = 252; // I won't use option
    const WILL  = 251; // I will use option
    const SB    = 250; // Subnegotiation
    const GA    = 249; // You may reverse the line
    const EL    = 248; // Erase the current line
    const EC    = 247; // Erase the current character
    const AYT   = 246; // Are you there
    const AO    = 245; // Abort output
    const IP    = 244; // Interrupt process
    const BREAK = 243; // Break
    const DM    = 242; // Data mark
    const NOP   = 241; // Nop
    const SE    = 240; // End subnegotiation
    const EOR   = 239; // End of record
    const ABORT = 238; // Abort process
    const SUSP  = 237; // Suspend process
    const xEOF  = 236; // End of file

    const TELOPT_ECHO        = 1;
    const TELOPT_SGA         = 3;
    const TELOPT_TTYPE       = 24;
    const TELOPT_EOR         = 25;
    const TELOPT_NAWS        = 31;
    const TELOPT_NEW_ENVIRON = 39;
    const TELOPT_CHARSET     = 42;
    const TELOPT_MSDP        = 69; // Mud Server Data Protocol
    const TELOPT_MSSP        = 70; // Mud Server Status Protocol
    const TELOPT_MCCP1       = 85;
    const TELOPT_MCCP2       = 86;
    const TELOPT_MCCP3       = 87;
    const TELOPT_MSP         = 90; // Mud Sound Protocol
    const TELOPT_MXP         = 91; // Mud Extension Protocol
    const TELOPT_ATCP        = 200;
    const TELOPT_GMCP        = 201;

    // Bits for Mud Terminal Type Standard
    const MTTS_ANSI          = 1 << 0;
    const MTTS_VT100         = 1 << 1;
    const MTTS_UTF8          = 1 << 2;
    const MTTS_256COLORS     = 1 << 3;
    const MTTS_MOUSETRACKING = 1 << 4;
    const MTTS_COLORPALETTE  = 1 << 5;
    const MTTS_SCREENREADER  = 1 << 6;
    const MTTS_PROXY         = 1 << 7;
    const MTTS_TRUECOLOR     = 1 << 8;
    const MTTS_MNES          = 1 << 9;
    const MTTS_MSLP          = 1 << 10;
    const MTTS_SSL           = 1 << 11;

    public function __construct(
        protected Descriptor $desc
    ) {

    }

    public function parseInput(string $input): array
    {
        $commands = [];
        $command = [];
        $normalInput = '';

        for ($i = 0; $i < strlen($input); $i++) {
            $d = ord($input[$i]);

            if (count($command) > 0) {
                $command[] = $d;

                $isSub = count($command) >= 2 && $command[1] == self::SB;
                $endSub = count($command) >= 2 && $d == self::SE &&
                    $command[count($command) - 2] == self::IAC;

                if ((!$isSub && count($command) == 3) || ($isSub && $endSub)) {
                    $commands[] = $command;
                    $command = [];
                }
            } else {
                if ($d == self::IAC) {
                    $command[] = $d;
                } else {
                    $normalInput .= $input[$i];
                }
            }
        }

        return [
            $commands,
            $normalInput
        ];
    }

    public function makeResponse(array $protocol): ?array
    {
        return null;
    }
}
