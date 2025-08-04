<?php
/**
 * Gauntlet MUD - Eat command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Items;

use Gauntlet\Action;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Template\FoodTemplate;
use Gauntlet\Util\Input;
use Gauntlet\Util\ItemFinder;

class Eat extends BaseCommand
{
    public function __construct(
        protected ItemFinder $finder,
        protected Action $action
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($input->empty()) {
            $player->outln('What do you wish to eat?');
            return;
        }

        $lists = [$player->getInventory()];
        $item = $this->finder->find($player, $input->get(0), $lists);

        if (!$item) {
            $player->outln('You are not carrying anything by that name.');
            return;
        } elseif (!($item->getTemplate() instanceof FoodTemplate)) {
            $player->outln('That does not seem to be edible.');
            return;
        }

        $nutrition = $item->getWeight() * 100;

        // Gain health and movement
        $player->setHealth(min($player->getMaxHealth(), $player->getHealth() + $nutrition));
        $player->setMove(min($player->getMaxMove(), $player->getMove() + $nutrition));

        $this->action->eat($player, $item);
    }

    public function getDescription(?string $subcmd): string
    {
        return 'Eat something. Generally eating food has beneficial effects such as restoring health. However some food may be harmful or even poisoned.';
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            '<item>',
        ];
    }

    public function getSeeAlso(?string $subcmd): array
    {
        return ['inventory'];
    }
}
