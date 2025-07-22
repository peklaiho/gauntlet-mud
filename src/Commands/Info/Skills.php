<?php
/**
 * Gauntlet MUD - Skills command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Info;

use Gauntlet\Player;
use Gauntlet\SkillMap;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Input;
use Gauntlet\Util\TableFormatter;

class Skills extends BaseCommand
{
    public const SKILLS = 'skills';
    public const SPELLS = 'spells';

    public function __construct(

    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        $skills = SkillMap::getSkills($player->getClass());

        $rows = [];
        foreach ($skills as $skillInfo) {
            $rows[] = [$skillInfo[0], $skillInfo[1]->value];
        }

        $headers = [
            'Level',
            ucfirst($player->getClass()->spellSkill())
        ];

        $format = TableFormatter::format($rows, $headers, [1]);

        foreach ($format as $row) {
            $player->outln($row);
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return "List $subcmd for your class.";
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            ''
        ];
    }

    public function canExecute(Player $player, ?string $subcmd): bool
    {
        return str_starts_with($subcmd, $player->getClass()->spellSkill());
    }
}
