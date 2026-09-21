<?php
/**
 * Gauntlet MUD - Spells
 * Copyright (C) 2017-2026 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Enum;

enum Spell: string
{
    // Cleric
    case MajorProtection = 'Major Protection';
    case MinorProtection = 'Minor Protection';
    case Regeneration = 'Regeneration';
    case WordOfRecall = 'Word of Recall';

    // Mage
    case MagicMissile = 'Magic Missile';
    case FireBolt = 'Firebolt';
    case ChillBones = 'Chill Bones';
    case FireBall = 'Fireball';
    case AlphaAndOmega = 'Alpha and Omega';
}
