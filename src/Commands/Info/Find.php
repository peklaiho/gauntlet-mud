<?php
/**
 * Gauntlet MUD - Find command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Info;

use Gauntlet\Collection;
use Gauntlet\Item;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Input;
use Gauntlet\Util\ItemFinder;

class Find extends BaseCommand
{
    public function __construct(
        protected ItemFinder $itemFinder
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($input->empty()) {
            $player->outln('Which item do you wish to locate?');
            return;
        }

        $lists = [$player->getRoom()->getItems(), $player->getInventory(), $player->getEquipment()];

        $keyword = $input->get(0);
        $player->outln("Search for items named '$keyword':");
        $count = 0;

        foreach ($lists as $list) {
            $count += $this->search($player, $list, $keyword, null);
        }

        if ($count == 0) {
            $player->outln('None!');
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return "Find all items matching the given name in current room, your inventory and equipment. This command will search inside containers as well.";
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            '<name>',
        ];
    }

    private function search(Player $player, Collection $list, string $keyword, ?Item $container): int
    {
        $count = 0;

        foreach ($list->getAll() as $item) {
            if ($player->canSeeItem($item)) {
                if ($item->getTemplate()->hasKeyword($keyword)) {
                    $player->outln("%s (%s)", $item->getTemplate()->getAName(), $item->getLocation(true));
                    $count++;
                }
                $count += $this->search($player, $item->getContents(), $keyword, $item);
            }
        }

        return $count;
    }
}
