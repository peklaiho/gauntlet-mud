<?php
/**
 * Gauntlet MUD - Flee command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Fight;

use Gauntlet\Fight;
use Gauntlet\Player;
use Gauntlet\Renderer;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Input;

class Flee extends BaseCommand
{
    public function __construct(
        protected Renderer $render,
        protected Fight $fight
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        $dir = $this->fight->flee($player);

        if ($dir) {
            $this->render->renderRoom($player, $player->getRoom(), true);
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return "Escape to an adjacent room, selected randomly. Can be used while fighting.";
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            '',
        ];
    }
}
