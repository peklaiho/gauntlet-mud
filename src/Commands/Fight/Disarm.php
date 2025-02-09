<?php
/**
 * Gauntlet MUD - Disarm command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Fight;

use Gauntlet\Act;
use Gauntlet\ActionFight;
use Gauntlet\Fight;
use Gauntlet\Player;
use Gauntlet\World;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Enum\Skill;
use Gauntlet\Util\Input;
use Gauntlet\Util\LivingFinder;
use Gauntlet\Util\Log;
use Gauntlet\Util\Random;

class Disarm extends BaseCommand
{
    public function __construct(
        protected Act $act,
        protected ActionFight $actionFight,
        protected Fight $fight,
        protected World $world
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if (!$player->checkInitiateViolence(true)) {
            return;
        } elseif ($input->empty()) {
            $player->outln("Who do you wish to disarm?");
            return;
        }

        $lists = [$player->getRoom()->getLiving()];
        $target = (new LivingFinder($player, $lists))
            ->excludeSelf()
            ->find($input->get(0));

        if (!$target) {
            $player->outln(MESSAGE_NOONE);
            return;
        } elseif (!$target->getWeapon() || !$player->canSeeItem($target->getWeapon())) {
            $this->act->toChar("@E is not wielding a weapon!", $player, null, $target);
            return;
        }

        if (!$player->checkInitiateViolenceAgainst($target, false)) {
            return;
        }

        if ($player->getAdminLevel()) {
            Log::admin($player->getName() . ' disarms ' . $target->getName() . ' in room ' . $player->getRoom()->getTemplate()->getId() . '.');
        }

        $chance = max(MIN_TO_HIT, 25 + ($player->getLevel() - $target->getLevel()));

        if (Random::percent($chance)) {
            $this->actionFight->disarm($player, $target);
            $this->world->itemToInventory($target->getWeapon(), $target);
        } else {
            $this->act->toChar("Your disarm attempt fails!", $player, null, $target);
            $this->act->toVict('@t attempts to disarm you but fails!', false, $player, null, $target);
            $this->act->toRoom('@t attempts to disarm @T but fails!', false, $player, null, $target, true);
        }

        $this->fight->specialAttack($player, $target, 0);
    }

    public function getDescription(?string $subcmd): string
    {
        return 'Disarm the weapon of your target (NPC or player).';
    }

    public function getUsage(?string $subcmd): array
    {
        return ['<monster | player>'];
    }

    public function getSeeAlso(?string $subcmd): array
    {
        return ['kill'];
    }

    public function canExecute(Player $player, ?string $subcmd): bool
    {
        return $player->hasSkill(Skill::Disarm);
    }
}
