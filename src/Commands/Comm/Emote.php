<?php
/**
 * Gauntlet MUD - Emote command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Comm;

use Gauntlet\Action;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Input;

class Emote extends BaseCommand
{
    public function __construct(
        protected Action $action
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($input->empty()) {
            $player->outln('What do you wish to emote?');
            return;
        }

        $message = $input->getWholeArgument(true);

        // End the sentence with a dot if it is not present
        $last = substr($message, strlen($message) - 1);
        if (!in_array($last, ['.', '!', '?'])) {
            $message .= '.';
        }

        $this->action->emote($player, $message);
    }

    public function getDescription(?string $subcmd): string
    {
        return "Display a message to all players in the room consisting of your name and the whole argument to this command. A dot is appended if the input does not end with a punctuation mark.";
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            '<message>'
        ];
    }
}
