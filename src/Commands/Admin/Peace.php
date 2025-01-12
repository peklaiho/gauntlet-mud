<?php
/**
 * Gauntlet MUD - Peace command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Admin;

use Gauntlet\Act;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Input;
use Gauntlet\Util\Log;

class Peace extends BaseCommand
{
    public function __construct(
        protected Act $act
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        Log::admin($player->getName() . ' stopped fights in room ' . $player->getRoom()->getTemplate()->getId() . '.');

        foreach ($player->getRoom()->getLiving()->getAll() as $living) {
            $living->setTarget(null);
        }

        $this->act->toChar("You make a gesture and everyone feels peaceful.", $player);
        $this->act->toRoom("@a makes a gesture and you feel peaceful.", true, $player);
    }

    public function getDescription(?string $subcmd): string
    {
        return "Bring peace to the current room by stopping all ongoing fights.";
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            ''
        ];
    }
}
