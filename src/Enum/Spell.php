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
    case MinorProtection = 'Minor Protection';
    case MajorProtection = 'Major Protection';

    // Mage
    case MagicMissile = 'Magic Missile';
    case FireBolt = 'Firebolt';
    case ChillBones = 'Chill Bones';
    case FireBall = 'Fireball';
    case AlphaAndOmega = 'Alpha and Omega';
}
