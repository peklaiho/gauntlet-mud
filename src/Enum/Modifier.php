<?php
/**
 * Gauntlet MUD - Modifiers
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Enum;

enum Modifier: string
{
    case Strength = 'str';
    case Dexterity = 'dex';
    case Intelligence = 'int';
    case Constitution = 'con';

    case Health = 'health';
    case Mana = 'mana';
    case Move = 'move';

    case Hit = 'hit';
    case Dodge = 'dodge';
    case Armor = 'armor';
    case Damage = 'damage';

    case Experience = 'exp';
}
