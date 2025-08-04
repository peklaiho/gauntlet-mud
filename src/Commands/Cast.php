<?php
/**
 * Gauntlet MUD - Cast command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands;

use Gauntlet\Act;
use Gauntlet\Living;
use Gauntlet\Player;
use Gauntlet\SkillMap;
use Gauntlet\SpellMap;
use Gauntlet\Util\Input;
use Gauntlet\Util\SpellParser;

class Cast extends BaseCommand
{
    public function __construct(
        protected Act $act
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($input->empty()) {
            $player->outln('Cast which spell?');
            return;
        }

        $spells = SkillMap::getSkillsForPlayer($player);
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
        } elseif (!$targetName) {
            $player->outln('This spell requires a target.');
            return;
        }

        $target = $spellInfo->findTarget($player, $targetName);

        if (!$target) {
            $player->outln(MESSAGE_NOONE);
            return;
        }

        $player->setMana($player->getMana() - $manaCost);

        // Show message about spell being cast
        $spellname = $spell->value;
        if ($target instanceof Living) {
            if ($player === $target) {
                $this->act->toChar("You close your eyes and utter the words '$spellname'!", $player);
                $this->act->toRoom("@t closes @s eyes and utters the words '$spellname'!", false, $player);
            } else {
                $this->act->toChar("You stare intently at @T and utter the words '$spellname'!", $player, null, $target);
                $this->act->toVict("@t stares intently at you and utters the words '$spellname'!", false, $player, null, $target);
                $this->act->toRoom("@t stares intently at @T and utters the words '$spellname'!", false, $player, null, $target, true);
            }
        } else {
            $this->act->toChar("You stare intently at @p and utter the words '$spellname'!", $caster, $target);
            $this->act->toRoom("@t stares intently at @o and utters the words '$spellname'!", false, $caster, $target);
        }

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
        return $player->getClass()->spellSkill() == 'spell';
    }

    public function getSeeAlso(?string $subcmd): array
    {
        return ['spells'];
    }
}
