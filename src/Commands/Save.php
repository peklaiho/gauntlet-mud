<?php
/**
 * Gauntlet MUD - Save command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands;

use Gauntlet\Data\IPlayerRepository;
use Gauntlet\Player;
use Gauntlet\Util\Input;

class Save extends BaseCommand
{
    public function __construct(
        protected IPlayerRepository $repo
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($this->repo->store($player)) {
            $player->outln('Your character has been saved.');
        } else {
            $player->outln('Error occured while saving your character.');
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return 'Persist your character to the database.';
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            '',
        ];
    }
}
