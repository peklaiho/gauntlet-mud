<?php
/**
 * Gauntlet MUD - Zone types
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Enum;

enum ZoneType: string
{
    // Zone has one global instance
    case Static = 'static';

    // Zone can have multiple instances
    case Dynamic = 'dynamic';
}
