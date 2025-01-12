<?php
/**
 * Gauntlet MUD - Wear command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Items;

use Gauntlet\Action;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Enum\EqSlot;
use Gauntlet\Util\Input;
use Gauntlet\Util\ItemFinder;

class Wear extends BaseCommand
{
    public const WEAR = 'wear';
    public const WIELD = 'wield';

    public function __construct(
        protected ItemFinder $finder,
        protected Action $action
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($input->empty()) {
            $player->outln("What do you wish to $subcmd?");
            return;
        }

        $lists = [$player->getInventory()];
        $item = $this->finder->find($player, $input->get(0), $lists);

        if ($item) {
            if ($subcmd == self::WIELD) {
                if (!$item->isWeapon()) {
                    $player->outln('You cannot wield that!');
                    return;
                } elseif ($player->getStr() < $item->getTemplate()->getRequiredStr()) {
                    $player->outln('You are not strong enough to wield that.');
                    return;
                } elseif ($player->getWeapon()) {
                    $player->outln('You are already wielding something.');
                    return;
                }

                $slot = EqSlot::Wield;
            } else {
                if (empty($item->getTemplate()->getSlots())) {
                    $player->outln('You cannot wear that!');
                    return;
                }

                $slot = $player->findEmptySlot($item);

                if (!$slot) {
                    $player->outln('You are already wearing something there.');
                    return;
                }
            }

            $this->action->wear($player, $item, $slot);
        } else {
            $player->outln('You are not carrying anything by that name.');
        }
    }

    public function getDescription(?string $subcmd): string
    {
        if ($subcmd == self::WEAR) {
            return 'Wear a piece of equipment from your inventory.';
        }

        return 'Wield a weapon from your inventory.';
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            "<item>",
        ];
    }

    public function getSeeAlso(?string $subcmd): array
    {
        if ($subcmd == self::WEAR) {
            return ['equipment', 'items', 'remove', 'wield'];
        } else {
            return ['equipment', 'items', 'remove', 'wear'];
        }
    }
}
