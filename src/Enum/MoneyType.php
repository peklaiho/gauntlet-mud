<?php
/**
 * Gauntlet MUD - Money types
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Enum;

enum MoneyType: string
{
    case Coins = 'coins';
    case Credits = 'credits';
}
