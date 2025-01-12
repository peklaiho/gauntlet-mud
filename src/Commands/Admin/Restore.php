<?php
/**
 * Gauntlet MUD - Restore command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Admin;

use Gauntlet\Act;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Input;
use Gauntlet\Util\LivingFinder;
use Gauntlet\Util\Log;

class Restore extends BaseCommand
{
    public function __construct(
        protected Act $act
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($input->empty()) {
            $player->outln("Who do you wish to restore?");
            return;
        }

        $lists = [$player->getRoom()->getLiving()];
        $target = (new LivingFinder($player, $lists))
            ->find($input->get(0));

        if (!$target) {
            $player->outln(MESSAGE_NOONE);
            return;
        }

        $health = $input->get(1, $target->getMaxHealth());

        if (!is_numeric($health) || $health < 1 || $health > 50_000) {
            $player->outln("Invalid health amount.");
            return;
        }

        Log::admin($player->getName() . ' restored health of ' . $target->getName() . ' to ' . $health . '.');

        $target->setHealth($health);

        // Also restore mana and movement for players
        if ($target->isPlayer()) {
            if ($target->getMana() < $target->getMaxMana()) {
                $target->setMana($target->getMaxMana());
            }
            if ($target->getMove() < $target->getMaxMove()) {
                $target->setMove($target->getMaxMove());
            }
        }

        $this->act->toChar("You restore health for @M.", $player, null, $target);
        $this->act->toChar("You feel reinvigorated!", $target);
    }

    public function getDescription(?string $subcmd): string
    {
        return "Restore monster or player to full health by default, or give amount of health as argument.";
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            '<monster | player> [health]',
        ];
    }
}
