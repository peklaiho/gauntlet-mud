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
        $player->outln('Players online:');

        $players = [];
        foreach ($this->lists->getLiving()->getAll() as $living) {
            if ($living->isPlayer() && $player->canSee($living) && $living->getDescriptor()) {
                $players[] = $living;
            }
        }

        usort($players, [$this, 'compare']);

        foreach ($players as $target) {
            if ($target->getAdminLevel()) {
                $output = sprintf('[ %4s ] ', $target->getAdminLevel()->abbrev());
            } else {
                $output = sprintf('[ %2d %s ] ', $target->getLevel(), ucfirst(substr($target->getClass()->value, 0, 1)));
            }

            $output .= $target->getName();

            if ($target->getTitle()) {
                $output .= ' ' . $target->getTitle();
            }

            if ($target->getAdminLevel()) {
                $output = $player->colorize($output, ColorPref::ADMIN);
            }

            $player->outln($output);
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

    protected function compare(Player $a, Player $b): int
    {
        // Compare admin level
        $adminA = $a->getAdminLevel() ? $a->getAdminLevel()->value : 0;
        $adminB = $b->getAdminLevel() ? $b->getAdminLevel()->value : 0;

        $result = $adminB - $adminA;
        if ($result != 0) {
            return $result;
        }

        // Compare level
        $result = $b->getLevel() - $a->getLevel();
        if ($result != 0) {
            return $result;
        }

        // Compare name
        return strcmp($a->getName(), $b->getName());
    }
}
