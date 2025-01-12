<?php
/**
 * Gauntlet MUD - Inventory command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Info;

use Gauntlet\Collection;
use Gauntlet\Player;
use Gauntlet\Renderer;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Input;

class Inventory extends BaseCommand
{
    public function __construct(
        protected Renderer $render
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        $keyword = $input->get(0, null);
        if ($keyword) {
            $player->outln("You are carrying items named '$keyword':");
            $items = new Collection();
            foreach ($player->getInventory()->getAll() as $item) {
                if ($item->getTemplate()->hasKeyword($keyword)) {
                    $items->add($item);
                }
            }
        } else {
            $player->outln('You are carrying:');
            $items = $player->getInventory();
        }

        $output = $this->render->renderItems($player, $items);

        if (!$output) {
            $player->outln('Nothing!');
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return 'List carried items. Optional argument can be given to display only matching items.';
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            '[name]',
        ];
    }

    public function getSeeAlso(?string $subcmd): array
    {
        return ['items'];
    }
}
