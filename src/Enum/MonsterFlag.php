<?php
/**
 * Gauntlet MUD - Monster flags
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Enum;

enum MonsterFlag: string
{
    case Animal = 'animal';
    case Human = 'human';
    case Sentinel = 'sentinel';
    case Shopkeeper = 'shopkeeper';
    case Vermin = 'vermin';
}
