<?php
/**
 * Gauntlet MUD - Sense command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Info;

use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Enum\RoomFlag;
use Gauntlet\Util\Input;

class Sense extends BaseCommand
{
    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if (!$player->canSeeRoom()) {
            $player->outln(MESSAGE_DARK);
            return;
        }

        $output = false;

        foreach ($player->getRoom()->getTemplate()->getFlags() as $flag) {
            if ($flag == RoomFlag::Peaceful) {
                $player->outln('You have a very peaceful feeling here.');
                $output = true;
            } elseif ($flag == RoomFlag::Regen) {
                $player->outln('You feel your health and energy recovering.');
                $output = true;
            }
        }

        if (!$output) {
            $player->outln('You do not sense anything out of the ordinary.');
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return 'Attempt to sense any special properties about the current room.';
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            '',
        ];
    }

    public function getSeeAlso(?string $subcmd): array
    {
        return ['look'];
    }
}
