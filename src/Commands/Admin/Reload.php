<?php
/**
 * Gauntlet MUD - Reload command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Admin;

use Gauntlet\HelpFiles;
use Gauntlet\Lists;
use Gauntlet\Player;
use Gauntlet\PlayerItems;
use Gauntlet\World;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Input;
use Gauntlet\Util\Log;

class Reload extends BaseCommand
{
    public function __construct(
        protected World $world,
        protected PlayerItems $playerItems,
        protected HelpFiles $helpFiles,
        protected Lists $lists
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if (strcasecmp($input->getCommand(), 'reload') != 0) {
            $player->outln("You must type the whole command to reload the world.");
            return;
        }

        Log::admin('World reload by ' . $player->getName() . '.');

        // Save all player items temporarily and restore them after reload
        foreach ($this->lists->getLiving()->getAll() as $living) {
            if ($living->isPlayer()) {
                $this->playerItems->saveItems($living);
            }
        }

        // Extract all items
        while (!$this->lists->getItems()->empty()) {
            $this->world->extractItem($this->lists->getItems()->first());
        }

        $players = [];

        // Extract monsters and detach players
        foreach ($this->lists->getLiving()->getAll() as $living) {
            if ($living->isPlayer()) {
                $players[] = $living;
                $this->world->detachLiving($living);
            } else {
                $this->world->extractLiving($living);
            }
        }

        // Extract zones and rooms
        foreach ($this->lists->getZones()->getAll() as $zone) {
            $this->world->extractZone($zone);
        }

        // Delete templates
        $this->world->deleteTemplates();

        // Reload everything from files
        $this->world->initialize();

        // Read help and info files
        $this->helpFiles->initialize();

        // Move players back into their starting rooms
        foreach ($players as $plr) {
            $plr->outln('The winds of change blow through the realm...');
            $startRoom = $this->world->getStartingRoom($plr);
            $this->world->livingToRoom($plr, $startRoom);

            // Also restore their items
            $this->playerItems->loadItems($plr);
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return "Reload the world files including zones, rooms, items and monsters. All currently loaded items (including player equipment) and monsters will be deleted.";
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            ''
        ];
    }
}
