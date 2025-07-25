<?php
/**
 * Gauntlet MUD - Affection types
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Enum;

enum AffectionType: string
{
    case Spell = 'spell';
    case Skill = 'skill';
}
