<?php
/**
 * Gauntlet MUD - Bury command
 * Copyright (C) 2017-2026 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Items;

use Gauntlet\Action;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Enum\ItemFlag;
use Gauntlet\Util\Input;
use Gauntlet\Util\ItemFinder;

class Bury extends BaseCommand
{
    public function __construct(
        protected ItemFinder $finder,
        protected Action $action
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($input->empty()) {
            $player->outln("What do you wish to bury?");
            return;
        }

        $lists = [$player->getRoom()->getItems(), $player->getInventory()];
        $corpse = $this->finder->find($player, $input->get(0), $lists);

        if (!$corpse) {
            $player->outln('There is nothing here by that name.');
        } elseif (!$corpse->getTemplate()->hasFlag(ItemFlag::MonsterCorpse)) {
            $player->outln('You can only bury monster corpses.');
        } else {
            $this->action->bury($player, $corpse);
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return 'Bury a corpse. Any items inside will drop on the ground.';
    }

    public function getUsage(?string $subcmd): array
    {
        return ['<corpse>'];
    }
}
