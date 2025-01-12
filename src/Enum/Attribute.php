<?php
/**
 * Gauntlet MUD - Character attributes
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Enum;

enum Attribute: string
{
    case Strength = 'str';
    case Dexterity = 'dex';
    case Intelligence = 'int';
    case Constitution = 'con';
}
