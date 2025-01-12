<?php
/**
 * Gauntlet MUD - Damage types
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Enum;

enum Damage: string
{
    case Physical = 'physical';
    case Fire = 'fire';
    case Cold = 'cold';
    case Electric = 'electric';
    case Poison = 'poison';
}
