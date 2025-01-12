<?php
/**
 * Gauntlet MUD - Put command
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

class Put extends BaseCommand
{
    public function __construct(
        protected ItemFinder $finder,
        protected Action $action,
        protected Act $act
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($input->count() < 2) {
            $player->outln('Put what in where?');
            return;
        }

        $itemName = $input->get(0);
        $containerName = $input->get(1);

        // Skip over 'in' if it was given
        if ($input->count() >= 3 && strcasecmp($containerName, 'in') == 0) {
            $containerName = $input->get(2);
        }

        $lists = [$player->getInventory(), $player->getEquipment(), $player->getRoom()->getItems()];
        $isContainer = fn ($a) => $a->isContainer();
        $container = $this->finder->find($player, $containerName, $lists, $isContainer);

        if (!$container) {
            $player->outln('There is no container here by that name.');
            return;
        }

        $capacity = $container->getTemplate()->getCapacity();

        if (strcasecmp($itemName, 'all') == 0) {
            $isFull = false;
            $noItems = true;

            foreach ($player->getInventory()->getAll() as $item) {
                if ($player->canSeeItem($item) && $item !== $container) {
                    $noItems = false;

                    if ($capacity >= 0 && ($item->getWeight() + $container->getWeightOfContents()) > $capacity) {
                        $isFull = true;
                    } else {
                        $this->action->putInContainer($player, $item, $container);
                    }
                }
            }

            if ($noItems) {
                $player->outln('You are not carrying anything to put inside it.');
            } elseif ($isFull) {
                $this->act->toChar('@p does not have enough space.', $player, $container);
            }
        } else {
            // Single item
            $lists = [$player->getInventory()];
            $item = $this->finder->find($player, $itemName, $lists);

            if (!$item) {
                $player->outln('You are not carrying anything by that name.');
            } elseif ($container === $item) {
                $player->outln('You cannot put an item inside itself.');
            } elseif ($capacity >= 0 && ($item->getWeight() + $container->getWeightOfContents()) > $capacity) {
                $this->act->toChar('@p does not fit inside @P.', $player, $item, $container);
            } else {
                $this->action->putInContainer($player, $item, $container);
            }
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return 'Put an item inside a container. The item must be in your inventory while ' .
            'the container can be worn or located in the current room. You can also put ' .
            'all items that you are carrying inside a container at once.';
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            "<item> ['in'] <container>",
            "'all' ['in'] <container>",
        ];
    }

    public function getSeeAlso(?string $subcmd): array
    {
        return ['inventory', 'items'];
    }
}
