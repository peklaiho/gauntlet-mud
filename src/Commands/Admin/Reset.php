<?php
/**
 * Gauntlet MUD - Reset command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Admin;

use Gauntlet\Lists;
use Gauntlet\Player;
use Gauntlet\ZoneReset;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Input;
use Gauntlet\Util\Log;

class Reset extends BaseCommand
{
    public function __construct(
        protected Lists $lists,
        protected ZoneReset $zoneReset
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($input->empty()) {
            $player->outln('Which zone do you wish to reset?');
            return;
        }

        $zone = $this->lists->getZones()->get($input->get(0));

        if ($zone) {
            Log::admin($player->getName() . " reset zone {$zone->getId()}: {$zone->getName()}");
            $player->outln("You reset zone {$zone->getId()}: {$zone->getName()}");
            $this->zoneReset->reset($zone, false);
        } else {
            $player->outln('No zone by that id.');
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return "Reset the given zone. Use 'query zones' to get a list of all zones.";
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            "<id>"
        ];
    }
}
