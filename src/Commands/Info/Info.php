<?php
/**
 * Gauntlet MUD - Info command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Info;

use Gauntlet\HelpFiles;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Input;
use Gauntlet\Util\Levenshtein;
use Gauntlet\Util\Log;
use Gauntlet\Util\Preferences;

class Info extends BaseCommand
{
    public function __construct(
        protected HelpFiles $helpFiles
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        $topics = $this->helpFiles->getInfoTopics();
        $search = $input->getWholeArgument(true);

        if (!$search) {
            $player->outln('Available topics:');
            $player->outWordTable($topics);
            return;
        }

        $matches = $this->helpFiles->getInfo($search);

        if (empty($matches)) {
            $player->outln('No information found for that topic.');
            $suggestion = Levenshtein::findClosest($search, $topics, 2);
            if ($suggestion) {
                $player->outln("Did you mean '$suggestion'?");
            }
            return;
        } elseif (count($matches) >= 2) {
            $player->outln("Info topics matching '%s':", $search);
            $player->outWordTable(array_keys($matches));
            return;
        }

        // One match
        if (key($matches) == 'Rules' && $input->count() > 1) {
            // Accept rules
            if (strtolower($input->get(1)) == 'accept') {
                $player->setAcceptedRules(true);
                $player->outln("Thank you for accepting the rules. Please enjoy the game!");
                Log::info($player->getName() . " has accepted the rules.");
            } else {
                $player->outln("Please type 'accept' as the second argument to accept the rules.");
            }
        } else {
            $player->outpr($player->highlight(current($matches)), true);
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return "Display information about the given topic, or display all available topics if no argument is given. Use 'info rules' to read the rules of the game and 'info rules accept' to accept them.";
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            '',
            '<topic>',
            "<'rules'> <'accept'>",
        ];
    }
}
