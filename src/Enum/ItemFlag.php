<?php
/**
 * Gauntlet MUD - Item flags
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Enum;

enum ItemFlag: string
{
    case MonsterCorpse = 'mcorpse';
    case PlayerCorpse = 'pcorpse';
    case Plural = 'plural'; // Treat as plural even if 1 item (pants, boots)
    case Trash = 'trash'; // Generic non-valuable item
}
