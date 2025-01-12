<?php
/**
 * Gauntlet MUD - Exits command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Info;

use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Enum\Direction;
use Gauntlet\Util\Input;

class Exits extends BaseCommand
{
    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if (!$player->canSeeRoom()) {
            $player->outln(MESSAGE_DARK);
            return;
        }

        $player->outln("Available exits:");
        $count = 0;

        foreach ($player->getRoom()->getExits() as $dirName => $exit) {
            $dir = Direction::from($dirName);
            $room = $exit->getTo();

            $door = '';
            if ($exit->isDoor()) {
                $door = ' (' . $exit->getTemplate()->getDoorName();

                if ($exit->isLocked()) {
                    $door .= ', locked)';
                } elseif ($exit->isClosed()) {
                    $door .= ', closed)';
                } else {
                    $door .= ', open)';
                }
            }

            if ($player->getAdminLevel()) {
                $flags = '';
                if ($exit->getTemplate()->getFlags()) {
                    $flags = ' [' . $exit->getTemplate()->renderFlags() . ']';
                }
                $player->outln("%-5s -> [%4d] %s%s%s", ucfirst($dir->name), $room->getTemplate()->getId(), $room->getTemplate()->getName(), $door, $flags);
            } else {
                if ($exit->isPassable($player)) {
                    $player->outln("%-5s -> %s%s", ucfirst($dir->name), $room->getTemplate()->getName(), $door);
                } else {
                    $player->outln("%-5s ->%s", ucfirst($dir->name), $door);
                }
            }

            $count++;
        }

        if (!$count) {
            $player->outln("None!");
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return "Display available exits to adjacent rooms.";
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
