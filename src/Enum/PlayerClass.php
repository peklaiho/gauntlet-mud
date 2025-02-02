<?php
/**
 * Gauntlet MUD - Player classes
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Enum;

enum PlayerClass: string
{
    case Mage = 'mage';
    case Rogue = 'rogue';
    case Shaman = 'shaman';
    case Warrior = 'warrior';

    public static function parse(string $val): ?PlayerClass
    {
        if (str_starts_with_case($val, 'm')) {
            return PlayerClass::Mage;
        } elseif (str_starts_with_case($val, 'r')) {
            return PlayerClass::Rogue;
        } elseif (str_starts_with_case($val, 's')) {
            return PlayerClass::Shaman;
        } elseif (str_starts_with_case($val, 'w')) {
            return PlayerClass::Warrior;
        }

        return null;
    }

    public static function listChoices(): string
    {
        return '(m)age, (r)ogue, (s)haman, (w)arrior';
    }

    public static function infoText(): array
    {
        return [
            '(M)age is the master of offensive magic that focuses on elements such as fire and cold.' .
                ' Mage can also inflict curses upon their enemies to weaken them.',
            '(R)ogue is the master of subterfuge, preferring to stay in the shadows and ambush' .
                ' unsuspecting enemies. Rogue also uses poison to weaken their enemies.',
            '(S)haman uses healing magic to cure wounds and other ailments.' .
                ' Shaman also wields offensive magic that is particularly effective against evil foes.',
            '(W)arrior is the master of physical combat. Warriors overwhelm their enemies with brute force.' .
                ' They can wield the deadliest weapons and gain additional attacks.',
        ];
    }
}
