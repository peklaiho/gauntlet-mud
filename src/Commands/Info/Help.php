<?php
/**
 * Gauntlet MUD - Help command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Info;

use Gauntlet\CommandMap;
use Gauntlet\HelpFiles;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\ColorPref;
use Gauntlet\Util\Input;
use Gauntlet\Util\Log;

class Help extends BaseCommand
{
    public function __construct(
        protected CommandMap $cmdMap,
        protected HelpFiles $helpFiles
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($input->empty()) {
            $player->outpr("Type 'commands' to see available commands. " .
                "Type 'help' followed by command to view information about it. " .
                "Type 'info' to learn about the background, places and inhabitants of this world.");
            $player->outln();

            $player->outln('Help is also available for the following topics:');
            $player->outWordTable($this->helpFiles->getHelpTopics());
            return;
        }

        // Check topics first
        $topic = $this->helpFiles->getHelp($input->get(0));
        if ($topic) {
            $player->outpr($topic, true);
            return;
        }

        $cmdInfo = $this->cmdMap->getCommand($input->get(0), $player->getAdminLevel());

        if ($cmdInfo) {
            $cmd = SERVICE_CONTAINER->get($cmdInfo->getName());
            $cmdName = $player->colorize($cmdInfo->getAlias(), ColorPref::HIGHLIGHT);

            if ($input->count() > 1) {
                $context = $cmd->getContextHelp($cmdInfo->getSubcmd());

                if ($context) {
                    foreach ($context as $key => $value) {
                        if (str_starts_with_case($key, $input->get(1))) {
                            $contextName = $player->colorize($key, ColorPref::HIGHLIGHT);
                            $player->outln("Information about the '$cmdName' command for context '$contextName':");
                            $player->outpr('* ' . $value);
                            return;
                        }
                    }

                    Log::info("Player {$player->getName()} requested missing help: " . $input->getWholeArgument(true));
                    $player->outln("The '$cmdName' command does not have help for the given context.");
                } else {
                    Log::info("Player {$player->getName()} requested missing help: " . $input->getWholeArgument(true));
                    $player->outln("The '$cmdName' command does not have context-specific help.");
                }
            } else {
                $player->outln("Information about the '$cmdName' command:");

                $player->outpr('* ' . $cmd->getDescription($cmdInfo->getSubcmd()));

                $player->outln('* Usage:');
                foreach ($cmd->getUsage($cmdInfo->getSubcmd()) as $usage) {
                    $player->outln('  ' . $cmdInfo->getAlias() . ' ' . $usage);
                }

                $seeAlso = $cmd->getSeeAlso($cmdInfo->getSubcmd());
                if ($seeAlso) {
                    $seeAlsoStr = implode(', ', array_map(fn ($a) => "'$a'", $seeAlso));
                    $player->outln('* See also: ' . $seeAlsoStr);
                }

                $context = $cmd->getContextHelp($cmdInfo->getSubcmd());
                if ($context) {
                    $player->outln('* Context-specific help is available. Keywords:');
                    $player->outWordTable(array_keys($context));
                }
            }
        } else {
            Log::info("Player {$player->getName()} requested missing help: " . $input->getWholeArgument(true));
            $player->outln('No help found for such command or topic.');
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return 'Display information about the given command or topic. Some commands have additional context-specific help that can be read by giving a second argument.';
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            '<command | topic>',
            '<command> <context>',
        ];
    }
}
