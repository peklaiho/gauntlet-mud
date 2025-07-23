<?php
/**
 * Gauntlet MUD - Cast command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands;

use Gauntlet\Magic;
use Gauntlet\Player;
use Gauntlet\SkillMap;
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

        // Damage spell requires a target
        if (!$info[1]) {
            $player->outln('This spell requires a target.');
            return;
        }

        $lists = [$player->getRoom()->getLiving()];
        $target = (new LivingFinder($player, $lists))
            ->excludeSelf()
            ->find($info[1]);

        if (!$target) {
            $player->outln(MESSAGE_NOONE);
            return;
        }

        // All ok, lets do it
        $this->magic->castDamageSpell($player, $target, $info[0]);
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
