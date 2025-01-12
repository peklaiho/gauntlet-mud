<?php
/**
 * Gauntlet MUD - Alias command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Interface;

use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Input;

class Alias extends BaseCommand
{
    public const ALIAS = 'alias';
    public const UNALIAS = 'unalias';

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        $aliases = $player->getAliases();

        if ($subcmd == self::ALIAS) {
            if ($input->empty()) {
                if (count($aliases) == 0) {
                    $player->outln('You do not have any aliases.');
                } else {
                    $len = max(array_map('strlen', array_keys($aliases)));

                    $player->outln('Aliases:');
                    foreach ($aliases as $key => $alias) {
                        $player->outln("%-{$len}s  ->  %-s", $key, $alias);
                    }
                }
            } elseif ($input->count() == 1) {
                $player->outln('Which command do you wish to alias?');
            } else {
                $name = $input->get(0);

                // Do not allow aliases which prevent managing aliases
                $reserved = ['alias', 'unalias'];
                foreach ($reserved as $res) {
                    if (str_starts_with_case($res, $name)) {
                        $player->outln('Unable to set alias which prevents managing aliases.');
                        return;
                    }
                }

                $aliases[$name] = $input->getWholeArgSkip(1, true);
                $player->setAliases($aliases);
                $player->outln('Ok.');
            }
        } else {
            if ($input->empty()) {
                $player->outln('Which alias do you wish to remove?');
            } else {
                if (array_key_exists($input->get(0), $aliases)) {
                    unset($aliases[$input->get(0)]);
                    $player->setAliases($aliases);
                    $player->outln('Ok.');
                } else {
                    $player->outln('No alias found by that name.');
                }
            }
        }
    }

    public function getDescription(?string $subcmd): string
    {
        if ($subcmd == self::ALIAS) {
            return 'Define aliases (shortcuts) for commands. You can use $1, $2 and so on as placeholders which are replaced by arguments.';
        } else {
            return 'Remove an alias.';
        }
    }

    public function getUsage(?string $subcmd): array
    {
        if ($subcmd == self::ALIAS) {
            return [
                '',
                '<name> <command>'
            ];
        } else {
            return [
                '<name>'
            ];
        }
    }

    public function getSeeAlso(?string $subcmd): array
    {
        if ($subcmd == self::ALIAS) {
            return ['unalias'];
        } else {
            return ['alias'];
        }
    }
}
