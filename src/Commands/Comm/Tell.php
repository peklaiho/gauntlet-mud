<?php
/**
 * Gauntlet MUD - Tell command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Comm;

use Gauntlet\Lists;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\ColorPref;
use Gauntlet\Util\Input;
use Gauntlet\Util\LivingFinder;
use Gauntlet\Util\Log;

class Tell extends BaseCommand
{
    public function __construct(
        protected Lists $lists
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($input->count() < 2) {
            $player->outln('What do you wish to tell, and to whom?');
            return;
        }

        $lists = [$this->lists->getLiving()];
        $target = (new LivingFinder($player, $lists))
            ->excludeMonsters()
            ->excludeSelf()
            ->find($input->get(0));

        if (!$target) {
            $player->outln(MESSAGE_NOONE);
            return;
        }

        $player->outln('Ok.');

        $name = $target->canSee($player) ? $player->getName() : 'Someone';
        $message = $input->getWholeArgSkip(1, true);
        $message = $target->colorize($message, ColorPref::TELL);

        $target->outln("%s tells you, '%s'", $name, $message);

        Log::comm(sprintf("%s tells %s, '%s'", $player->getName(), $target->getName(), $message));
    }

    public function getDescription(?string $subcmd): string
    {
        return 'Speak the given phrase to another player.';
    }

    public function getUsage(?string $subcmd): array
    {
        return ['<player> <phrase>'];
    }

    public function getSeeAlso(?string $subcmd): array
    {
        return ['gossip', 'say'];
    }
}
