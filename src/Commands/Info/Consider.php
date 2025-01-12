<?php
/**
 * Gauntlet MUD - Consider command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Info;

use Gauntlet\Act;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Input;
use Gauntlet\Util\LivingFinder;

class Consider extends BaseCommand
{
    public function __construct(
        protected Act $act
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($input->empty()) {
            $player->outln("Who do you wish to consider?");
            return;
        }

        $lists = [$player->getRoom()->getLiving()];
        $target = (new LivingFinder($player, $lists))
            ->excludeSelf()
            ->find($input->get(0));

        if (!$target) {
            $player->outln(MESSAGE_NOONE);
            return;
        }

        $advantage = $player->getLevel() - $target->getLevel();

        $message = match(true) {
            $advantage >= 10 => '@E is no match for you.',
            $advantage >= 8 => 'You are greatly more experienced than @M.',
            $advantage >= 6 => 'You are much more experienced than @M.',
            $advantage >= 4 => 'You are more experienced than @M.',
            $advantage >= 2 => 'You are little more experienced than @M.',
            $advantage >= -1 => 'You have roughly equal experience with @M.',
            $advantage >= -3 => '@E is little more experienced than you.',
            $advantage >= -5 => '@E is more experienced than you.',
            $advantage >= -7 => '@E is much more experienced than you.',
            $advantage >= -9 => '@E is greatly more experienced than you.',
            default => 'You are no match for @M.'
        };

        $this->act->toChar($message, $player, null, $target);
    }

    public function getDescription(?string $subcmd): string
    {
        return 'Consider the combat experience of an opponent relative to yourself.';
    }

    public function getUsage(?string $subcmd): array
    {
        return ['<monster | player>'];
    }
}
