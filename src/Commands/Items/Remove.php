<?php
/**
 * Gauntlet MUD - Remove command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Items;

use Gauntlet\Action;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Input;
use Gauntlet\Util\ItemFinder;

class Remove extends BaseCommand
{
    public function __construct(
        protected ItemFinder $finder,
        protected Action $action
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($input->empty()) {
            $player->outln('What do you wish to remove?');
            return;
        }

        $lists = [$player->getEquipment()];
        $item = $this->finder->find($player, $input->get(0), $lists);

        if ($item) {
            $this->action->remove($player, $item);
        } else {
            $player->outln('You are not wearing anything by that name.');
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return 'Stop using a worn piece of equipment or a weapon and place it in your inventory.';
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            "<item>",
        ];
    }

    public function getSeeAlso(?string $subcmd): array
    {
        return ['equipment', 'items', 'wear', 'wield'];
    }
}
