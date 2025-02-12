<?php
/**
 * Gauntlet MUD - Bury command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Items;

use Gauntlet\Act;
use Gauntlet\Lists;
use Gauntlet\Player;
use Gauntlet\World;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Enum\ItemFlag;
use Gauntlet\Util\Input;
use Gauntlet\Util\ItemFinder;
use Gauntlet\Util\Log;

class Bury extends BaseCommand
{
    public function __construct(
        protected ItemFinder $finder,
        protected Act $act,
        protected World $world,
        protected Lists $lists
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
            $player->outln('You can only bury monster corpses for now.');
        } else {
            // Drop items to ground
            while (!$corpse->getContents()->empty()) {
                $item = $corpse->getContents()->first();
                if ($item->getTemplate()->hasFlag(ItemFlag::Trash)) {
                    // Trash we can just delete
                    $this->world->extractItem($item);
                } else {
                    $this->act->toChar('@o drops from @P.', $player, $item, $corpse);
                    $this->act->toRoom('@o drops from @P.', false, $player, $item, $corpse);
                    $this->world->itemToRoom($item, $player->getRoom());
                }
            }

            $this->act->toChar('You bury @p.', $player, $corpse);
            $this->act->toRoom('@t buries @o.', false, $player, $corpse);

            $this->world->extractItem($corpse);

            // Give some coins as reward
            $template = $this->lists->getMonsterTemplates()->get(-$corpse->getTemplate()->getId());
            $player->addCoins($template->getLevel());
            $player->outln('The gods reward you with some coin.');

            Log::money($player->getName() . ' receives ' . $template->getLevel() . ' coins as burial reward.');
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
