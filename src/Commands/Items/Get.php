<?php
/**
 * Gauntlet MUD - Get command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Items;

use Gauntlet\Act;
use Gauntlet\Action;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Input;
use Gauntlet\Util\ItemFinder;

class Get extends BaseCommand
{
    public function __construct(
        protected ItemFinder $finder,
        protected Act $act,
        protected Action $action
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($input->count() >= 2) {
            // Get from container
            $itemName = $input->get(0);
            $containerName = $input->get(1);

            // Skip over 'from' if it was given
            if ($input->count() >= 3 && strcasecmp($containerName, 'from') == 0) {
                $containerName = $input->get(2);
            }

            $lists = [$player->getInventory(), $player->getEquipment(), $player->getRoom()->getItems()];
            $isContainer = fn ($a) => $a->isContainer();
            $container = $this->finder->find($player, $containerName, $lists, $isContainer);

            if (!$container) {
                $player->outln('There is no container here by that name.');
                return;
            }

            if (strcasecmp($itemName, 'all') == 0) {
                $isEmpty = true;
                $tooMuchWeight = false;

                foreach ($container->getContents()->getAll() as $item) {
                    if ($player->canSeeItem($item)) {
                        $isEmpty = false;

                        if ($container->getRoom() && !$player->canCarry($item, true)) {
                            $tooMuchWeight = true;
                        } else {
                            $this->action->getFromContainer($player, $item, $container);
                        }
                    }
                }

                if ($isEmpty) {
                    $this->act->toChar('There is nothing inside @p.', $player, $container);
                } elseif ($tooMuchWeight) {
                    $player->outln('You are unable to carry that much weight.');
                }
            } else {
                // Get single item
                $lists = [$container->getContents()];
                $item = $this->finder->find($player, $itemName, $lists);

                if ($item) {
                    // Check weight if container is in room
                    if ($container->getRoom() && !$player->canCarry($item, true)) {
                        $player->outln('You are unable to carry that much weight.');
                    } else {
                        $this->action->getFromContainer($player, $item, $container);
                    }
                } else {
                    $this->act->toChar('There is nothing by that name inside @p.', $player, $container);
                }
            }
        } else {
            // Get from ground
            if ($input->empty()) {
                $player->outln('What do you wish to get?');
                return;
            }

            $lists = [$player->getRoom()->getItems()];
            $item = $this->finder->find($player, $input->get(0), $lists);

            if ($item) {
                if ($player->canCarry($item, true)) {
                    $this->action->get($player, $item);
                } else {
                    $player->outln('You are unable to carry that much weight.');
                }
            } else {
                $player->outln(MESSAGE_NOTHING);
            }
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return 'Pick up an item from the ground or from inside a container. You can also get all items from a container at once.';
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            '<item>',
            "<item> ['from'] <container>",
            "'all' ['from'] <container>"
        ];
    }

    public function getSeeAlso(?string $subcmd): array
    {
        return ['inventory', 'items'];
    }
}
