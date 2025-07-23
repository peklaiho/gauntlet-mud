<?php
/**
 * Gauntlet MUD - Modifiers
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Enum;

enum Modifier: string
{
    // Attributes
    case Strength = 'str';
    case Dexterity = 'dex';
    case Intelligence = 'int';
    case Constitution = 'con';

    // Vitals
    case Health = 'health';
    case Mana = 'mana';
    case Move = 'move';

    // Physical combat modifiers
    case Hit = 'hit';
    case Dodge = 'dodge';
    case Armor = 'armor';
    case Damage = 'damage';

    // Magic combat modifiers
    case SpellHit = 'spell-hit';
    case SpellDodge = 'spell-dodge';
    case SpellDamage = 'spell-damage';

    // Resistances
    case ResistMagic = 'res-magic';
    case ResistPhysical = 'res-physical';
    case ResistFire = 'res-fire';
    case ResistCold = 'res-cold';
    case ResistElectric = 'res-electric';
    case ResistPoison = 'res-poison';

    // Other
    case Experience = 'exp';
}
