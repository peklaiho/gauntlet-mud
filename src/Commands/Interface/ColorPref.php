<?php
/**
 * Gauntlet MUD - ColorPref command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Interface;

use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Color;
use Gauntlet\Util\ColorPref as CP;
use Gauntlet\Util\Input;
use Gauntlet\Util\Preferences;

class ColorPref extends BaseCommand
{
    private static $map = [
        [CP::HIGHLIGHT, 'Highlight keywords'],
        [CP::PROMPT, 'Prompt'],

        [CP::ROOMNAME, 'Room names'],
        [CP::ROOMDESC, 'Room descriptions'],
        [CP::ROOMEXIT, 'Room exits'],
        [CP::ROOMNPC, 'NPCs and monsters'],
        [CP::ROOMOBJ, 'Items in rooms'],
        [CP::ROOMPLAYER, 'Players'],

        [CP::TELL, 'Tells (communication)'],
        [CP::GOSSIP, 'Gossip (communication)'],
        [CP::OOC, 'OOC (communication)'],
    ];

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if (!$player->getPreference(Preferences::COLOR)) {
            $player->outln("Use 'pref color' to enable colors first.");
            return;
        }

        if ($input->empty()) {
            $player->outln("Color preferences:");
            foreach (self::$map as $i => $info) {
                $txt = $player->colorize('[%s] %s', $info[0]);
                $player->outln('%2d: ' . $txt, $i + 1,
                    $player->getColorPref()->get($info[0]), $info[1]);
            }
        } elseif ($input->count() == 1) {
            if (strtolower($input->get(0)) == 'default') {
                $player->getColorPref()->setDefaults();
                $player->outln('Color preferences set to default values.');
            } else {
                $player->outln('Give number and color as arguments to set color preference.');
            }
        } else {
            if (!filter_var($input->get(0), FILTER_VALIDATE_INT) ||
                $input->get(0) <= 0 || $input->get(0) > count(self::$map)) {
                $player->outln('Invalid preference number. Try again.');
            } elseif (!Color::isValid($input->get(1))) {
                $player->outln('Invalid color value. Try again.');
            } else {
                $player->getColorPref()->set(self::$map[$input->get(0) - 1][0], $input->get(1));
                $player->outln('Ok.');
            }
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return "Manage your color preferences. Allowed color values: n=none, k=black, r=red, g=green, y=yellow, b=blue, m=magenta, c=cyan. Capital letter indicates bold/bright color. Give 'default' as argument to restore default colors.";
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            '',
            "<'default'>",
            '<number> <color>'
        ];
    }

    public function getSeeAlso(?string $subcmd): array
    {
        return ['pref'];
    }
}
