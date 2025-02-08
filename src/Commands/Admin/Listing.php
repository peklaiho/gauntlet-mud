<?php
/**
 * Gauntlet MUD - List command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Admin;

use Gauntlet\Collection;
use Gauntlet\Lists;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Input;

class Listing extends BaseCommand
{
    public function __construct(
        protected Lists $lists
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($input->empty()) {
            $player->outln('What do you wish to query?');
            return;
        }

        if (str_starts_with_case('items', $input->get(0))) {
            if ($input->count() < 2) {
                $zone = $player->getRoom()->getZone();
                if (!$zone) {
                    $player->outln('Current room does not belong to any zone.');
                    return;
                }

                $filter = fn ($a) => $zone->getTemplate()->ownsRoomId($a->getId());
            } else {
                $filter = fn ($a) => $a->hasKeyword($input->get(1));
            }

            if (!$this->listTemplates($player, $this->lists->getItemTemplates(), $filter)) {
                $player->outln('No items found.');
            }
        } elseif (str_starts_with_case('monsters', $input->get(0))) {
            if ($input->count() < 2) {
                $zone = $player->getRoom()->getZone();
                if (!$zone) {
                    $player->outln('Current room does not belong to any zone.');
                    return;
                }

                $filter = fn ($a) => $zone->getTemplate()->ownsRoomId($a->getId());
            } else {
                $filter = fn ($a) => $a->hasKeyword($input->get(1));
            }

            if (!$this->listTemplates($player, $this->lists->getMonsterTemplates(), $filter)) {
                $player->outln('No monsters found.');
            }
        } elseif (str_starts_with_case('rooms', $input->get(0))) {
            if ($input->count() < 2) {
                $zone = $player->getRoom()->getZone();
                if (!$zone) {
                    $player->outln('Current room does not belong to any zone.');
                    return;
                }

                $filter = fn ($a) => $zone->getTemplate()->ownsRoomId($a->getTemplate()->getId());
            } else {
                $filter = fn ($a) => str_contains_case($a->getName(), $input->get(1));
            }

            if (!$this->listRooms($player, $filter)) {
                $player->outln('No rooms found.');
            }
        } elseif (str_starts_with_case('zones', $input->get(0))) {
            foreach ($this->lists->getZoneTemplates()->getAll() as $zoneTemplate) {
                $player->outln("[%4d] %s", $zoneTemplate->getId(), $zoneTemplate->getName());
                foreach ($this->lists->getZones()->getAll() as $zone) {
                    if ($zone->getTemplate()->getId() == $zoneTemplate->getId()) {
                        $player->outln(".%4d  %s", $zone->getMagicNumber(), strval($zone->getOwner()));
                    }
                }
            }
        } else {
            $player->outln('Unknown type. What do you wish to query?');
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return 'List rooms, items or monsters in the current zone. Or give keyword to search globally.';
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            "'items' [keyword]",
            "'monsters' [keyword]",
            "'rooms' [keyword]",
            "'zones'"
        ];
    }

    private function listRooms(Player $player, callable $filter): bool
    {
        $found = false;

        foreach ($this->lists->getRooms()->getAll() as $room) {
            if ($filter($room)) {
                $player->outln("[%4d] %s (%s)", $room->getTemplate()->getId(), $room->getTemplate()->getName(),
                    ucfirst($room->getTemplate()->getTerrain()->value));
                $found = true;
            }
        }

        return $found;
    }

    private function listTemplates(Player $player, Collection $list, callable $filter): bool
    {
        $found = false;

        foreach ($list->getAll() as $template) {
            if ($filter($template)) {
                $loaded = $template->getCount() ? ' (' . $template->getCount() . ')' : '';
                $player->outln("[%4d] %s%s", $template->getId(), $template->getName(), $loaded);
                $found = true;
            }
        }

        return $found;
    }
}
