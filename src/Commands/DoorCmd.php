<?php
/**
 * Gauntlet MUD - Door commands
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands;

use Gauntlet\Action;
use Gauntlet\Player;
use Gauntlet\Enum\Direction;
use Gauntlet\Enum\ExitFlag;
use Gauntlet\Util\Input;

class DoorCmd extends BaseCommand
{
    public const OPEN = 'open';
    public const CLOSE = 'close';
    public const LOCK = 'lock';
    public const UNLOCK = 'unlock';

    public function __construct(
        protected Action $action
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if (!$player->canSeeRoom()) {
            $player->outln(MESSAGE_DARK);
            return;
        } elseif ($input->empty()) {
            $player->outln("What do you wish to $subcmd?");
            return;
        }

        $targetName = $input->get(0);
        $targetDir = $input->get(1, null);

        $matches = [];

        foreach ($player->getRoom()->getExits() as $dirName => $exit) {
            $dir = Direction::from($dirName);

            if (!$exit->isDoor()) {
                continue;
            }

            if (!str_starts_with_case($exit->getTemplate()->getDoorName(), $targetName)) {
                continue;
            }

            if ($targetDir && !str_starts_with_case($dir->name, $targetDir)) {
                continue;
            }

            $matches[] = [$dir, $exit];
        }

        if (count($matches) == 0) {
            $player->outln(MESSAGE_NOTHING);
        } elseif (count($matches) > 1) {
            $player->outln('Which ' . $matches[0][1]->getTemplate()->getDoorName() . ' do you wish to ' . $subcmd . '?');
        } else {
            $dir = $matches[0][0];
            $exit = $matches[0][1];

            if ($subcmd == self::OPEN) {
                if ($exit->getTemplate()->hasFlag(ExitFlag::NoOp)) {
                    $player->outln("You do not know how to operate it.");
                } elseif ($exit->isLocked()) {
                    $player->outln("The {$exit->getTemplate()->getDoorName()} is locked.");
                } elseif (!$exit->isClosed()) {
                    $player->outln("The {$exit->getTemplate()->getDoorName()} is already open.");
                } else {
                    $this->action->open($player, $dir);
                }
            } elseif ($subcmd == self::CLOSE) {
                if ($exit->getTemplate()->hasFlag(ExitFlag::NoOp)) {
                    $player->outln("You do not know how to operate it.");
                } elseif ($exit->isClosed()) {
                    $player->outln("The {$exit->getTemplate()->getDoorName()} is already closed.");
                } else {
                    $this->action->close($player, $dir);
                }
            } elseif ($subcmd == self::LOCK) {
                if (!$exit->isClosed()) {
                    $player->outln("The {$exit->getTemplate()->getDoorName()} is open, close it first.");
                } elseif ($exit->isLocked()) {
                    $player->outln("The {$exit->getTemplate()->getDoorName()} is already locked.");
                } elseif (!$exit->getTemplate()->getKeyId()) {
                    $player->outln("The {$exit->getTemplate()->getDoorName()} does not seem to have a keyhole.");
                } elseif (!$player->hasKey($exit)) {
                    $player->outln("You do not have the correct key for the {$exit->getTemplate()->getDoorName()}.");
                } else {
                    $this->action->lock($player, $dir);
                }
            } else {
                if (!$exit->isLocked()) {
                    $player->outln("The {$exit->getTemplate()->getDoorName()} is not locked.");
                } elseif (!$exit->getTemplate()->getKeyId()) {
                    $player->outln("The {$exit->getTemplate()->getDoorName()} does not seem to have a keyhole.");
                } elseif (!$player->hasKey($exit)) {
                    $player->outln("You do not have the correct key for the {$exit->getTemplate()->getDoorName()}.");
                } else {
                    $this->action->unlock($player, $dir);
                }
            }
        }
    }

    public function getDescription(?string $subcmd): string
    {
        if ($subcmd == self::OPEN) {
            return 'Open a door or similar barrier between two rooms.';
        } elseif ($subcmd == self::CLOSE) {
            return 'Close a door or similar barrier between two rooms.';
        } elseif ($subcmd == self::LOCK) {
            return 'Lock a door or similar barrier between two rooms. You must have the correct key in your inventory.';
        } else {
            return 'Unlock a door or similar barrier between two rooms. You must have the correct key in your inventory.';
        }
    }

    public function getUsage(?string $subcmd): array
    {
        return ['<keyword> [direction]'];
    }

    public function getSeeAlso(?string $subcmd): array
    {
        if ($subcmd == self::OPEN) {
            return ['close', 'lock', 'unlock'];
        } elseif ($subcmd == self::CLOSE) {
            return ['lock', 'open', 'unlock'];
        } elseif ($subcmd == self::LOCK) {
            return ['close', 'open', 'unlock'];
        } else {
            return ['close', 'open', 'lock'];
        }
    }
}
