<?php
/**
 * Gauntlet MUD - Drain command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Admin;

use Gauntlet\Act;
use Gauntlet\Fight;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Input;
use Gauntlet\Util\LivingFinder;
use Gauntlet\Util\Log;

class Drain extends BaseCommand
{
    public function __construct(
        protected Act $act,
        protected Fight $fight
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($input->count() < 2) {
            $player->outln("Who do you wish to drain and by how much?");
            return;
        }

        $lists = [$player->getRoom()->getLiving()];
        $target = (new LivingFinder($player, $lists))
            ->find($input->get(0));

        if (!$target) {
            $player->outln(MESSAGE_NOONE);
            return;
        }

        // Prevent draining equal or higher level admins
        if (!$player->checkInitiateViolenceAgainst($target, true)) {
            return;
        }

        $health = $input->get(1);
        if (!is_numeric($health) || $health < 1 || $health > 50_000) {
            $player->outln("Invalid health amount.");
            return;
        }

        Log::admin($player->getName() . ' drained ' . $health . ' health from ' . $target->getName() . '.');

        $this->act->toChar("You touch @T and drain @S health.", $player, null, $target);
        $this->act->toVict("You feel your health draining away as @t touches you!", false, $player, null, $target);
        $this->act->toRoom("The health of @T is drained as @t touches @M!", true, $player, null, $target, true);

        $this->fight->damage($target, $health, $player);
    }

    public function getDescription(?string $subcmd): string
    {
        return "Drain the health of a monster or player by the specified amount.";
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            '<monster | player> <health>',
        ];
    }
}
