<?php
/**
 * Gauntlet MUD - Title command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands;

use Gauntlet\Player;
use Gauntlet\Util\Input;

class Title extends BaseCommand
{
    public function __construct(

    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        $title = $input->getWholeArgument(true);

        if ($title) {
            if (strcasecmp($title, 'default') == 0) {
                $title = 'the ' . ucfirst($player->getClass()->value);
            }

            $player->setTitle($title);
            $player->outln('Your title has been updated.');
        } else {
            $player->setTitle(null);
            $player->outln('Your title has been disabled.');
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return "Set the title of your character. No argument disables title. 'Default' sets it to default value.";
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            '',
            '<title>',
            "'default'",
        ];
    }
}
