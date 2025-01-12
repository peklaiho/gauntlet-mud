<?php
/**
 * Gauntlet MUD - Exit flags
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Enum;

enum ExitFlag: string
{
    case NoMonster = 'nomonster';
    case NoOp = 'noop';
}
