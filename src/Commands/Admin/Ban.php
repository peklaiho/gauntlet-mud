<?php
/**
 * Gauntlet MUD - Ban command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Admin;

use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\BanList;
use Gauntlet\Util\Input;
use Gauntlet\Util\Log;
use Gauntlet\Util\TableFormatter;

class Ban extends BaseCommand
{
    public function __construct(
        protected BanList $bans
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($input->empty()) {
            $rows = [];

            $list = $this->bans->list();
            foreach ($list as $ip => $info) {
                $rows[] = [
                    $ip,
                    $info['by'],
                    date('Y-m-d', $info['at'])
                ];
            }

            $rows = TableFormatter::format($rows, ['IP', 'By', 'On'], [0, 1, 2]);

            foreach ($rows as $row) {
                $player->outln($row);
            }
        } elseif (str_starts_with_case('add', $input->get(0))) {
            if ($input->count() < 2) {
                $player->outln("Which IP do you wish to ban?");
            } else {
                if ($this->bans->add($input->get(1), $player->getName())) {
                    $player->outln("IP address '%s' has been banned.", $input->get(1));
                    Log::admin($player->getName() . " banned IP: " . $input->get(1));
                } else {
                    $player->outln("Ban already exists for that IP.");
                }
            }
        } elseif (str_starts_with_case('remove', $input->get(0))) {
            if ($input->count() < 2) {
                $player->outln("Which IP do you wish to un-ban?");
            } else {
                if ($this->bans->remove($input->get(1))) {
                    $player->outln("Ban on IP '%s' was removed.", $input->get(1));
                    Log::admin($player->getName() . " removed ban from IP: " . $input->get(1));
                } else {
                    $player->outln("That IP is not banned.");
                }
            }
        } else {
            $player->outln("Unknown argument for ban.");
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return "List, add or remove banned IP addresses.";
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            "",
            "'add' <ip>",
            "'remove' <ip>"
        ];
    }
}
