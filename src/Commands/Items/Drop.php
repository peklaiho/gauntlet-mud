<?php
/**
 * Gauntlet MUD - Drop command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Items;

use Gauntlet\Action;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Input;
use Gauntlet\Util\ItemFinder;
use Gauntlet\Util\Log;

class Drop extends BaseCommand
{
    public const DROP = 'drop';
    public const DISCARD = 'discard';

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
        } elseif ($subcmd == self::DISCARD && strcasecmp($input->getCommand(), self::DISCARD) != 0) {
            $player->outln("You must type the whole command to discard an item.");
            return;
        }

        $lists = [$player->getInventory()];
        $item = $this->finder->find($player, $input->get(0), $lists);

        if ($item) {
            if ($subcmd == self::DROP) {
                if ($player->getAdminLevel()) {
                    Log::admin($player->getName() . ' drops ' . $item->getTemplate()->getAName() .
                        ' in room ' . $player->getRoom()->getTemplate()->getId() . '.');
                }
                $this->action->drop($player, $item);
            } else {
                // Have to confirm if there is something inside
                if (!$item->getContents()->empty()) {
                    $confirm = $input->get(1, '');
                    if (strcasecmp($confirm, 'yes') != 0) {
                        $player->outln("There is something inside. Give 'yes' as second argument to discard it and the contents.");
                        return;
                    }
                }

                $this->action->discard($player, $item);
            }
        } else {
            $player->outln('You are not carrying anything by that name.');
        }
    }

    public function getDescription(?string $subcmd): string
    {
        if ($subcmd == self::DROP) {
            return 'Drop an item from your inventory on the ground.';
        }

        return 'Discard (destroy) an item from your inventory.';
    }

    public function getUsage(?string $subcmd): array
    {
        return ['<item>'];
    }

    public function getSeeAlso(?string $subcmd): array
    {
        if ($subcmd == self::DROP) {
            return ['discard', 'inventory', 'items'];
        } else {
            return ['drop', 'inventory', 'items'];
        }
    }
}
