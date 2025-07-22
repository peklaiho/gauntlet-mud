<?php
/**
 * Gauntlet MUD - Skills
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Enum;

enum Skill: string
{
    // Rogue
    case Backstab = 'backstab';

    // Warrior
    case Disarm = 'disarm';
    case Rescue = 'rescue';
    case SecondAttack = 'second attack';
    case ThirdAttack = 'third attack';
}
