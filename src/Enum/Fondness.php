<?php
/**
 * Gauntlet MUD - Fondness of one factions towards another
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Enum;

enum Fondness: int
{
    case Loved = 3;
    case Loyal = 2;
    case Liked = 1;
    case Neutral = 0;
    case Disliked = -1;
    case Hated = -2;
    case Enemy = -3;
}
