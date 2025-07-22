<?php
/**
 * Gauntlet MUD - Spells
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Enum;

enum Spell: string
{
    // Cleric
    case MinorProtection = 'minor protection';
    case MajorProtection = 'major protection';

    // Mage
    case MagicMissile = 'magic missile';
    case FireBolt = 'firebolt';
    case ChillBones = 'chill bones';
    case FireBall = 'fireball';
    case AlphaAndOmega = 'alpha and omega';
}
