<?php
/**
 * Gauntlet MUD - Attack command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Fight;

use Gauntlet\ActionFight;
use Gauntlet\Fight;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Input;
use Gauntlet\Util\LivingFinder;
use Gauntlet\Util\Log;

class Attack extends BaseCommand
{
    public function __construct(
        protected ActionFight $actionFight,
        protected Fight $fight
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if (!$player->checkInitiateViolence(true)) {
            return;
        } elseif ($input->empty()) {
            $player->outln("Who do you wish to attack?");
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

        if (!$player->checkInitiateViolenceAgainst($target, true)) {
            return;
        }

        if ($player->getAdminLevel()) {
            Log::admin($player->getName() . ' attacked ' . $target->getName() . ' in room ' . $player->getRoom()->getTemplate()->getId() . '.');
        }

        $this->actionFight->attack($player, $target);
        $this->fight->attack($player, $target);
    }

    public function getDescription(?string $subcmd): string
    {
        return 'Start a fight with your target (NPC or player).';
    }

    public function getUsage(?string $subcmd): array
    {
        return ['<monster | player>'];
    }

    public function getSeeAlso(?string $subcmd): array
    {
        return ['assist', 'consider', 'flee'];
    }
}
