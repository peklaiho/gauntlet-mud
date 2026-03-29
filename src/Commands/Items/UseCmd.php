<?php
/**
 * Gauntlet MUD - Use command
 * Copyright (C) 2026 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Items;

use Gauntlet\Action;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Input;
use Gauntlet\Util\ItemFinder;

class UseCmd extends BaseCommand
{
    public function __construct(
        protected ItemFinder $finder,
        protected Action $action
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($input->empty()) {
            $player->outln("What do you wish to use?");
            return;
        }

        $lists = [$player->getInventory(), $player->getEquipment()];
        $item = $this->finder->find($player, $input->get(0), $lists);

        if (!$item) {
            $player->outln('You are not carrying anything by that name.');
        }

        if ($item->isLightSource()) {
            if ($item->getLightEnabled()) {
                $item->setLightEnabled(false);
                $this->action->light($player, $item, false);
            } elseif ($item->getTemplate()->hasUnlimitedFuel() ||
                $item->getLightSpentFuel() < $item->getTemplate()->getFuel()) {
                $item->setLightEnabled(true);
                $this->action->light($player, $item, true);
            } else {
                $player->outln('It has burned out.');
            }
            return;
        }

        // No suitable way to use the item found
        // Lets recommend a more appropriate command
        if ($item->isArmor()) {
            $player->outln('You could try wearing it.');
        } elseif ($item->isBulletinBoard()) {
            $player->outln("Try using the 'board' command instead.");
        } elseif ($item->isFood()) {
            $player->outln('You could try eating it.');
        } elseif ($item->isWeapon()) {
            $player->outln('You could try wielding it.');
        } else {
            $player->outln('You do not find any suitable use for it.');
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return 'Use an item.';
    }

    public function getUsage(?string $subcmd): array
    {
        return ['<item>'];
    }

    public function getSeeAlso(?string $subcmd): array
    {
        return ['eat', 'wear', 'wield'];
    }
}
