<?php
/**
 * Gauntlet MUD - Cast command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands;

use Gauntlet\Player;
use Gauntlet\SkillMap;
use Gauntlet\Util\Input;
use Gauntlet\Util\SpellParser;

class Cast extends BaseCommand
{
    public function __construct(

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
