<?php
/**
 * Gauntlet MUD - Move command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands;

use Gauntlet\ActionMove;
use Gauntlet\Player;
use Gauntlet\Renderer;
use Gauntlet\Enum\Direction;
use Gauntlet\Util\Input;

class Move extends BaseCommand
{
    public function __construct(
        protected Renderer $render,
        protected ActionMove $actionMove
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($player->getTarget()) {
            $player->outln("You are fighting for your life!");
            return;
        }

        $dir = Direction::from($subcmd);
        $ex = $player->getRoom()->getExit($dir);

        if ($ex) {
            if ($ex->isPassable($player)) {
                $room = $this->actionMove->move($player, $dir);
                if ($room) {
                    $this->render->renderRoom($player, $room, true);
                }
            } else {
                $player->outln("The {$ex->getTemplate()->getDoorName()} is closed.");
            }
        } else {
            $player->outln("There is no exit in that direction.");
        }
    }

    public function getDescription(?string $subcmd): string
    {
        $dirName = Direction::from($subcmd)->name();
        return "Move $dirName to the adjacent room.";
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            '',
        ];
    }

    public function getSeeAlso(?string $subcmd): array
    {
        return ['movement'];
    }
}
