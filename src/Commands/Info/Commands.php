<?php
/**
 * Gauntlet MUD - Commands command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Info;

use Gauntlet\CommandMap;
use Gauntlet\Player;
use Gauntlet\Socials;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Input;

class Commands extends BaseCommand
{
    public const COMMANDS = 'commands';
    public const SOCIALS = 'socials';

    public function __construct(
        protected CommandMap $cmds,
        protected Socials $socials
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($subcmd == self::COMMANDS) {
            $list = $this->cmds->getList($player);
        } else {
            $list = $this->socials->list();
        }

        if ($subcmd == self::COMMANDS && $player->getAdminLevel() && $input->hasFlag('a')) {
            $player->outln("Available admin commands:");
            $list = $this->cmds->getList($player, true);
        } elseif (!$input->empty()) {
            $name = $input->get(0);
            $player->outln("Available %s starting with '$name':", $subcmd);
            $list = array_values(array_filter($list, function ($cmd) use ($name) {
                return str_starts_with_case($cmd, $name);
            }));
        } else {
            $player->outln('Available %s:', $subcmd);
        }

        if ($list) {
            $player->outWordTable($list);
        } else {
            $player->outln('None!');
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return "List available $subcmd. Optional argument can given to display only matching $subcmd.";
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            '[name]'
        ];
    }
}
