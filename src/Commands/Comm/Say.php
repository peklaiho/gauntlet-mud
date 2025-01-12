<?php
/**
 * Gauntlet MUD - Say command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Comm;

use Gauntlet\Action;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Input;
use Gauntlet\Util\Log;

class Say extends BaseCommand
{
    public function __construct(
        protected Action $action
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($input->empty()) {
            $player->outln('What do you wish to say?');
            return;
        }

        $message = $input->getWholeArgument(true);

        $this->action->say($player, $message);

        Log::comm(sprintf("%s says, '%s'", $player->getName(), $message));
    }

    public function getDescription(?string $subcmd): string
    {
        return 'Speak the given phrase in a voice that it is heard by people in the same room.';
    }

    public function getUsage(?string $subcmd): array
    {
        return ['<phrase>'];
    }

    public function getSeeAlso(?string $subcmd): array
    {
        return ['gossip', 'tell'];
    }
}
