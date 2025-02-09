<?php
/**
 * Gauntlet MUD - Rescue command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Fight;

use Gauntlet\Act;
use Gauntlet\ActionFight;
use Gauntlet\Fight;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Enum\Skill;
use Gauntlet\Util\Input;
use Gauntlet\Util\LivingFinder;
use Gauntlet\Util\Log;

class Rescue extends BaseCommand
{
    public function __construct(
        protected Act $act,
        protected ActionFight $actionFight,
        protected Fight $fight
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if (!$player->checkInitiateViolence(true)) {
            return;
        } elseif ($input->empty()) {
            $player->outln("Who do you wish to rescue?");
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

        $hasAttackers = false;
        $success = false;

        // Loop through all living in the room
        foreach ($defender->getRoom()->getLiving()->getAll() as $attacker) {
            // Skip defender and player
            if ($attacker === $defender || $attacker === $player) {
                continue;
            }
            // Skip if attacker is not targeting defender
            if ($attacker->getTarget() !== $defender) {
                continue;
            }

            $hasAttackers = true;

            // Skip invisible
            if (!$player->canSee($attacker)) {
                continue;
            }
            // Skip for other reasons
            if (!$player->checkInitiateViolenceAgainst($attacker, false)) {
                continue;
            }

            // Success, set the target to player
            $attacker->setTarget($player);
            $success = true;
        }

        if ($success) {
            if ($player->getAdminLevel()) {
                Log::admin($player->getName() . ' rescued ' . $defender->getName() . ' in room ' . $player->getRoom()->getTemplate()->getId() . '.');
            }

            $this->actionFight->rescue($player, $defender);
        } elseif ($hasAttackers) {
            $this->act->toChar('You are unable to rescue @M.', $player, null, $defender);
        } else {
            $this->act->toChar('@E is not currently fighting.', $player, null, $defender);
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return 'Rescue your target (NPC or player) by turning the attention of aggressors to yourself.';
    }

    public function getUsage(?string $subcmd): array
    {
        return ['<monster | player>'];
    }

    public function getSeeAlso(?string $subcmd): array
    {
        return ['assist', 'kill'];
    }

    public function canExecute(Player $player, ?string $subcmd): bool
    {
        return $player->hasSkill(Skill::Rescue);
    }
}
