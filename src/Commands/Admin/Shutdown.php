<?php
/**
 * Gauntlet MUD - Shutdown command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Admin;

use Gauntlet\Player;
use Gauntlet\MainLoop;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Input;
use Gauntlet\Util\Log;

class Shutdown extends BaseCommand
{
    public function __construct(
        protected MainLoop $mainLoop
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if (strcasecmp($input->getCommand(), 'shutdown') != 0) {
            $player->outln("You must type the whole command to shut down the game.");
            return;
        }

        Log::admin('Shutdown by ' . $player->getName() . '.');

        $this->mainLoop->stop();
    }

    public function getDescription(?string $subcmd): string
    {
        return 'Shut down the game.';
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            '',
        ];
    }
}
