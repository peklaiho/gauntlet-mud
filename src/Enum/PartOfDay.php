<?php
/**
 * Gauntlet MUD - Parts of day
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Enum;

enum PartOfDay: string
{
    case Dawn = 'dawn';
    case Morning = 'morning';
    case Afternoon = 'afternoon';
    case Evening = 'evening';
    case Dusk = 'dusk';
    case Night = 'night';

    public function messageToPlayers(): ?string
    {
        return match($this) {
            PartOfDay::Dawn => 'The darkness of the night gives way to the first light of dawn.',
            PartOfDay::Morning => 'The first rays of the sun become visible as it rises in the east.',
            PartOfDay::Afternoon => 'The sun reaches its highest point in the sky marking noon.',
            PartOfDay::Evening => 'The sun moves lower in the sky indicating the start of evening.',
            PartOfDay::Dusk => 'The sun disappears under the horizon as it sets in the west.',
            PartOfDay::Night => 'The last light of dusk disappears and the darkness of the night sets in.',
        };
    }
}
