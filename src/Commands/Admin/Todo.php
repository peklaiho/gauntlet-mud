<?php
/**
 * Gauntlet MUD - Todo command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Admin;

use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Input;

class Todo extends BaseCommand
{
    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        $zone = $player->getRoom()->getZone();

        // Possible if error in world files
        if (!$zone) {
            $player->outln("Current room does not belong to any zone.");
            return;
        }

        $output = false;

        $player->outln('Total of %d rooms in this zone.', $zone->getRooms()->count());

        foreach ($zone->getRooms()->getAll() as $room) {
            $message = null;

            $desc = $room->getTemplate()->getLongDesc();
            if (!$desc) {
                $message = 'Desc missing';
            } elseif (strlen($desc) < 200) {
                $message = 'Desc too short (' . strlen($desc) . ')';
            } elseif (strlen($desc) > 650) {
                $message = 'Desc too long (' . strlen($desc) . ')';
            }

            if ($message) {
                $player->outln('[%4d] %s :: %s', $room->getTemplate()->getId(), $room->getTemplate()->getName(), $message);
                $output = true;
            }
        }

        if (!$output) {
            $player->outln('None!');
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return "List rooms with missing room description in the current zone.";
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            ''
        ];
    }
}
