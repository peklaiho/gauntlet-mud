<?php
/**
 * Gauntlet MUD - Debug command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Admin;

use Gauntlet\Experience;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Color;
use Gauntlet\Util\Input;

class Debug extends BaseCommand
{
    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($input->empty()) {
            $player->outln('Debug what?');
            return;
        }

        if (str_starts_with_case('color', $input->get(0))) {
            $colors = [];
            for ($i = 0; $i < 256; $i++) {
                $colors[] = sprintf('%sColor%03d%s', Color::color256($i), $i, Color::getCode(Color::RESET));
            }

            for ($i = 0; $i < 32; $i++) {
                $row = array_slice($colors, $i * 8, 8);
                $player->outln(implode(' ', $row));
            }
        } elseif (str_starts_with_case('exp', $input->get(0))) {
            $player->outln('Level    Exp     Total');
            $table = Experience::getExpTable();
            $total = 0;
            for ($i = 0; $i < count($table); $i++) {
                $total += $table[$i];
                $player->outln('%2d  %8d  %8d', $i + 1, $table[$i], $total);
            }
        } else {
            $player->outln('Unknown subcmd.');
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return 'Misc debug info.';
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            "",
        ];
    }
}
