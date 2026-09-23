<?php
/**
 * Gauntlet MUD - Cast command
 * Copyright (C) 2017-2026 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands;

use Gauntlet\Act;
use Gauntlet\Action;
use Gauntlet\Item;
use Gauntlet\Living;
use Gauntlet\Player;
use Gauntlet\SkillMap;
use Gauntlet\SpellMap;
use Gauntlet\Spell\AffectionSpell;
use Gauntlet\Util\Input;
use Gauntlet\Util\SpellParser;

class Cast extends BaseCommand
{
    public function __construct(
        protected Action $action,
        protected Act $act
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($input->empty()) {
            $player->outln('Cast which spell?');
            return;
        }

        $spells = SkillMap::getAvailableSkillsForPlayer($player, true);
        $info = SpellParser::parse($input, $spells);

        if (!$info) {
            $player->outln('You do not know any spell by that name.');
            return;
        }

        list($spell, $targetName) = $info;
        $spellInfo = SpellMap::get($spell);
        $manaCost = $player->getAdminLevel() ? 0 : $spellInfo->manaCost();

        if ($player->getMana() < $manaCost) {
            $player->outln('You do not have enough mana.');
            return;
        }

        $target = $spellInfo->findTarget($player, $targetName);

        if (!$target) {
            if ($targetName) {
                $player->outln(MESSAGE_NOONE);
            } else {
                $player->outln('This spell requires a target.');
            }
            return;
        }

        // Already affected by higher-level spell?
        if ($spellInfo instanceof AffectionSpell && $spellInfo->getHigherTierSpell() &&
            $target->getSpellAffection($spellInfo->getHigherTierSpell())) {
            if ($target instanceof Item) {
                $player->outln('It is already affected by a higher-level version of the spell.');
            } elseif ($target === $player) {
                $player->outln('You are already affected by a higher-level version of the spell.');
            } else {
                $this->act->toChar('@E is already affected by a higher-level version of the spell.', $player, null, $target);
            }
            return;
        }

        $player->setMana($player->getMana() - $manaCost);

        // Show message about spell being cast
        $this->action->cast($player, $target, $spell);

        $spellInfo->cast($player, $target);
    }

    public function getDescription(?string $subcmd): string
    {
        return 'Cast a spell.';
    }

    public function getUsage(?string $subcmd): array
    {
        return ['<spell> [target]'];
    }

    public function canExecute(Player $player, ?string $subcmd): bool
    {
        return $player->getAdminLevel() || $player->getClass()->spellSkill() == 'spell';
    }

    public function getSeeAlso(?string $subcmd): array
    {
        return ['spells'];
    }
}
