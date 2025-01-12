<?php
/**
 * Gauntlet MUD - Room flags
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Enum;

enum RoomFlag: string
{
    case Bank = 'bank';
    case Dark = 'dark';
    case Light = 'light';
    case Mail = 'mail';
    case Peaceful = 'peaceful';
    case Regen = 'regen';
}
