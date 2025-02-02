<?php
/**
 * Gauntlet MUD - Who command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Info;

use Gauntlet\Lists;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\ColorPref;
use Gauntlet\Util\Input;

class Who extends BaseCommand
{
    public function __construct(
        protected Lists $lists
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        $output = false;

        $player->outln('Players online:');

        foreach ($this->lists->getLiving()->getAll() as $living) {
            if ($living->isPlayer() && $player->canSee($living) && $living->getDescriptor()) {
                $name = $living->getName();
                if ($living->getAdminLevel()) {
                    $name .= ' (' . $living->getAdminLevel()->name() . ')';
                    $name = $player->colorize($name, ColorPref::ADMIN);
                }
                if ($player->getTitle()) {
                    $name .= ' ' . $player->getTitle();
                }
                $player->outln($name);
                $output = true;
            }
        }

        if (!$output) {
            $player->outln('None!');
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return 'Display players that are currently playing.';
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            "",
        ];
    }
}
