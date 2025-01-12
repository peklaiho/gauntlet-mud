<?php
/**
 * Gauntlet MUD - Date command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Info;

use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\GameTime;
use Gauntlet\Util\Input;

class Date extends BaseCommand
{
    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        $gt = GameTime::now();

        $player->outln('Today is the %s day of %s, year %d.', ordinal_indicator($gt->day()), $gt->monthName(), $gt->year());
        $player->outln('It is %s and current time is %s.', $gt->getPartOfDay()->value, $gt->time12h());

        if ($player->getAdminLevel()) {
            $player->outln('Current UTC time is %s.', gmdate('Y-m-d H:i:s'));
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return 'Show the current date and time.';
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            "",
        ];
    }
}
