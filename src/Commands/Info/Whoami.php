<?php
/**
 * Gauntlet MUD - Whoami command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Info;

use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Input;

class Whoami extends BaseCommand
{
    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        $player->outln($player->getName());
    }

    public function getDescription(?string $subcmd): string
    {
        return 'Display the name of your character.';
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            "",
        ];
    }
}
