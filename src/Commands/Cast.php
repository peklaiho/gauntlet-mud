<?php
/**
 * Gauntlet MUD - Cast command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands;

use Gauntlet\Affection;
use Gauntlet\Magic;
use Gauntlet\Player;
use Gauntlet\SkillMap;
use Gauntlet\Enum\AffectionType;
use Gauntlet\Enum\Modifier;
use Gauntlet\Enum\Spell;
use Gauntlet\Util\Input;
use Gauntlet\Util\LivingFinder;
use Gauntlet\Util\SpellParser;

class Cast extends BaseCommand
{
    public function __construct(
        protected Magic $magic
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

        // Damage spell requires a target
        if (!$targetName) {
            $player->outln('This spell requires a target.');
            return;
        }

        $lists = [$player->getRoom()->getLiving()];
        $target = (new LivingFinder($player, $lists))
            ->find($targetName);

        if (!$target) {
            $player->outln(MESSAGE_NOONE);
            return;
        }

        if ($spell == Spell::MinorProtection) {
            $aff = new Affection(AffectionType::Spell, $spell, time() + 30);
            $aff->setMod(Modifier::Armor, 10);
            if ($target->isPlayer()) {
                $aff->setCallback(function () use ($target) {
                    $target->outln('You no longer feel protected.');
                });
            }
            $target->addAffection($aff);
            $player->outln('They are now protected.');
            return;
        }

        // Damage spells
        $this->magic->castDamageSpell($player, $target, $spell);
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
