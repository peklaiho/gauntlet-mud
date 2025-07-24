<?php
/**
 * Gauntlet MUD - Backstab command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Fight;

use Gauntlet\Act;
use Gauntlet\ActionFight;
use Gauntlet\Fight;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Enum\ItemFlag;
use Gauntlet\Enum\Skill;
use Gauntlet\Util\Input;
use Gauntlet\Util\LivingFinder;
use Gauntlet\Util\Log;

class Backstab extends BaseCommand
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
            $player->outln("Who do you wish to backstab?");
            return;
        }

        $weapon = $player->getWeapon();
        if (!$weapon) {
            $player->outln('Backstab with what? Try wielding a weapon first.');
            return;
        } elseif (!$weapon->getTemplate()->hasFlag(ItemFlag::Backstab)) {
            $player->outln('Your weapon does not seem suitable for backstabbing.');
            return;
        }

        $lists = [$player->getRoom()->getLiving()];
        $target = (new LivingFinder($player, $lists))
            ->excludeSelf()
            ->find($input->get(0));

        if (!$target) {
            $player->outln(MESSAGE_NOONE);
            return;
        } elseif ($target->getTarget()) {
            $this->act->toChar('@E is already fighting!', $player, null, $target);
            return;
        }

        if (!$player->checkInitiateViolenceAgainst($target, true)) {
            return;
        }

        if ($player->getAdminLevel()) {
            Log::admin($player->getName() . ' backstabs ' . $target->getName() . ' in room ' . $player->getRoom()->getTemplate()->getId() . '.');
        }

        if ($this->fight->canHit($player, $target)) {
            $damage = $this->fight->getAttackDamage($player, $target);
            $damage *= $this->fight->getBackstabMultiplier($player);
            $this->actionFight->backstab($player, $target);
        } else {
            $damage = 0;
            $this->act->toChar("Your backstab misses!", $player, null, $target);
            $this->act->toVict('@t attempts to stab you in the back but misses!', false, $player, null, $target);
            $this->act->toRoom('@t attempts to stab @T in the back but misses!', false, $player, null, $target, true);
        }

        $this->fight->specialAttack($player, $target, $damage);
    }

    public function getDescription(?string $subcmd): string
    {
        return 'Backstab your victim inflicting high damage.';
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
        return $player->hasSkill(Skill::Backstab);
    }
}
