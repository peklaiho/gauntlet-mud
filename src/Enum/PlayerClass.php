<?php
/**
 * Gauntlet MUD - Player classes
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Enum;

enum PlayerClass: string
{
    case Healer = 'healer';
    case Mage = 'mage';
    case Rogue = 'rogue';
    case Warrior = 'warrior';

    public static function parse(string $val): ?PlayerClass
    {
        if (str_starts_with_case($val, 'h')) {
            return PlayerClass::Healer;
        } elseif (str_starts_with_case($val, 'm')) {
            return PlayerClass::Mage;
        } elseif (str_starts_with_case($val, 'r')) {
            return PlayerClass::Rogue;
        } elseif (str_starts_with_case($val, 'w')) {
            return PlayerClass::Warrior;
        }

        return null;
    }

    public static function listChoices(): string
    {
        return '(h)ealer, (m)age, (r)ogue, (w)arrior';
    }

    public static function infoText(): array
    {
        return [
            '(H)ealer uses healing magic to cure wounds and other ailments.' .
                ' Healer also wields offensive magic that is particularly effective against evil foes.',
            '(M)age is the master of offensive magic that focuses on elements such as fire and cold.' .
                ' Mage can also inflict curses upon their enemies to weaken them.',
            '(R)ogue is the master of subterfuge, preferring to stay in the shadows and ambush' .
                ' unsuspecting enemies. Rogue also uses poison to weaken their enemies.',
            '(W)arrior is the master of physical combat. Warriors overwhelm their enemies with brute force.' .
                ' They can wield the deadliest weapons and gain additional attacks.',
        ];
    }
}
