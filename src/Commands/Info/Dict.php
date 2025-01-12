<?php
/**
 * Gauntlet MUD - Dict command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Info;

use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\ColorPref;
use Gauntlet\Util\Input;

class Dict extends BaseCommand
{
    public const DICTIONARY = 'wn';
    public const THESAURUS = 'moby-thesaurus';

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($input->empty()) {
            $player->outln('Which word you wish to look up?');
            return;
        }

        $word = $input->getWholeArgument(true);

        // Check that we have only normal characters
        if (!preg_match('/^[a-zA-Z ]+$/', $word)) {
            $player->outln('Word contains invalid characters. Try again.');
            return;
        }

        $wordc = $player->colorize($word, ColorPref::HIGHLIGHT);
        $player->outln("Searching for definition of '$wordc':");

        $command = "dict -h localhost -d $subcmd -f '$word'";
        @exec($command, $output, $status);

        if ($status == 0) {
            for ($i = 3; $i < count($output); $i++) {
                $txt = substr($output[$i], 6);
                if ($txt) {
                    // Remove { }
                    $txt = str_replace('{', '', $txt);
                    $txt = str_replace('}', '', $txt);

                    $player->outln($txt);
                }
            }
        } elseif ($status == 20) {
            $player->outln('No definition found.');
        } elseif ($status == 21) {
            $player->outln('Close match found, check your spelling.');
        } else {
            $player->outln("Error with status code $status.");
        }
    }

    public function getDescription(?string $subcmd): string
    {
        if ($subcmd == self::DICTIONARY) {
            return 'Look up a word from dictionary.';
        } else {
            return 'Look up a word from thesaurus.';
        }
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            "<word>",
        ];
    }
}
