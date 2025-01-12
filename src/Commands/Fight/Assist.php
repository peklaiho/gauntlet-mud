<?php
/**
 * Gauntlet MUD - Assist command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Fight;

use Gauntlet\Act;
use Gauntlet\Action;
use Gauntlet\Fight;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Input;
use Gauntlet\Util\LivingFinder;
use Gauntlet\Util\Log;

class Assist extends BaseCommand
{
    public function __construct(
        protected Act $act,
        protected Action $action,
        protected Fight $fight
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if (!$player->checkInitiateViolence(true)) {
            return;
        } elseif ($input->empty()) {
            $player->outln("Who do you wish to assist?");
            return;
        }

        $lists = [$player->getRoom()->getLiving()];
        $defender = (new LivingFinder($player, $lists))
            ->excludeSelf()
            ->find($input->get(0));

        if (!$defender) {
            $player->outln(MESSAGE_NOONE);
            return;
        }

        $attacker = $defender->getTarget();

        if (!$attacker) {
            $this->act->toChar('@E is not currently fighting.', $player, null, $defender);
            return;
        } elseif (!$player->checkInitiateViolenceAgainst($attacker, true)) {
            return;
        }

        if ($player->getAdminLevel()) {
            Log::admin($player->getName() . ' attacked ' . $attacker->getName() . ' in room ' . $player->getRoom()->getTemplate()->getId() . '.');
        }

        $this->action->assist($player, $defender);
        $this->fight->attack($player, $attacker);
    }

    public function getDescription(?string $subcmd): string
    {
        return 'Assist your target (NPC or player) by fighting against their aggressor.';
    }

    public function getUsage(?string $subcmd): array
    {
        return ['<monster | player>'];
    }

    public function getSeeAlso(?string $subcmd): array
    {
        return ['kill', 'rescue'];
    }
}
