<?php
/**
 * Gauntlet MUD - Player classes
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Enum;

enum PlayerClass: string
{
    case Cleric = 'cleric';
    case Mage = 'mage';
    case Rogue = 'rogue';
    case Warrior = 'warrior';

    public static function parse(string $val): ?PlayerClass
    {
        if (str_starts_with_case($val, 'c')) {
            return self::Cleric;
        } elseif (str_starts_with_case($val, 'm')) {
            return self::Mage;
        } elseif (str_starts_with_case($val, 'r')) {
            return self::Rogue;
        } elseif (str_starts_with_case($val, 'w')) {
            return self::Warrior;
        }

        return null;
    }

    public static function listChoices(): string
    {
        return '(c)leric, (m)age, (r)ogue, (w)arrior';
    }

    public static function infoText(): array
    {
        return [
            '(C)leric uses healing magic to cure wounds and other ailments.' .
                ' Clerics also employ protective spells to shield themselves from harm.',
            '(M)age is the master of offensive magic that focuses on elements such as fire and cold.' .
                ' Mages can deal lots of damage fast, but need to rest to regain their mana.',
            '(R)ogue is the master of subterfuge, preferring to stay in the shadows and ambush' .
                ' unsuspecting enemies. Rogues also use poisons to weaken their enemies.',
            '(W)arrior is the master of physical combat. Warriors overwhelm their enemies with brute force.' .
                ' They can wield the deadliest weapons and gain additional attacks.',
        ];
    }

    public function spellSkill(): string
    {
        if ($this == self::Cleric || $this == self::Mage) {
            return 'spell';
        }

        return 'skill';
    }
}
