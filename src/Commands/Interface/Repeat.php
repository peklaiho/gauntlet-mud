<?php
/**
 * Gauntlet MUD - Repeat command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Interface;

use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Input;

class Repeat extends BaseCommand
{
    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        // This is handled elsewhere, but we get here if there was no previous input
        $player->outln('You have no previous input to repeat.');
    }

    public function getDescription(?string $subcmd): string
    {
        return 'Repeat the last command. Optional arguments replace arguments from the previous input starting from the end.' .
            ' For example if the previous command was "cmd 1 2 3", running "! 0" will become "cmd 1 2 0".';
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            '[arg1] [arg2] ...',
        ];
    }

    public function getSeeAlso(?string $subcmd): array
    {
        return ['alias'];
    }
}
