<?php
/**
 * Gauntlet MUD - Handler for player items
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Enum\EqSlot;
use Gauntlet\Enum\ItemFlag;
use Gauntlet\Util\Log;

class PlayerItems
{
    public function __construct(
        protected World $world,
        protected Lists $lists
    ) {

    }

    public function saveItems(Player $player): void
    {
        $player->setSavedInventory($this->doSaveItems($player->getInventory(), false));
        $player->setSavedEquipment($this->doSaveItems($player->getEquipment(), true));
    }

    public function loadItems(Player $player): void
    {
        $this->doLoadItems($player, $player->getSavedInventory(), 'inventory');
        $this->doLoadItems($player, $player->getSavedEquipment(), 'equipment');
    }

    private function doLoadItems(Player $player, array $items, $target): void
    {
        foreach ($items as $key => $info) {
            $template = $this->lists->getItemTemplates()->get($info['id']);

            if ($template) {
                if ($target == 'inventory') {
                    $item = $this->world->loadItemToInventory($template, $player);
                } elseif ($target == 'equipment') {
                    $slot = EqSlot::tryFrom($key);
                    if ($slot) {
                        $item = $this->world->loadItemToEquipment($template, $player, $slot);
                    } else {
                        Log::error("Invalid equipment slot in player items: $key");
                    }
                } else {
                    $item = $this->world->loadItemToContainer($template, $target);
                }

                $this->doLoadItems($player, $info['contents'] ?? [], $item);
            } else {
                Log::error("Unable to load item {$info['id']} for player {$player->getName()}.");
            }
        }
    }

    private function doSaveItems(Collection $list, bool $useKey): array
    {
        $results = [];

        foreach ($list->getAll() as $key => $item) {
            // Skip corpses
            if ($item->getTemplate()->hasAnyFlag(
                ItemFlag::MonsterCorpse,
                ItemFlag::PlayerCorpse
            )) {
                continue;
            }

            $data = [
                'id' => $item->getTemplate()->getId()
            ];

            if (!$item->getContents()->empty()) {
                $data['contents'] = $this->doSaveItems($item->getContents(), false);
            }

            if ($useKey) {
                $results[$key] = $data;
            } else {
                $results[] = $data;
            }
        }

        return $results;
    }
}
