<?php
/**
 * Gauntlet MUD - Terrain types
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Enum;

enum Terrain: string
{
    case Inside = 'inside';
    case Town = 'town';
    case City = 'city';
    case Road = 'road';
    case Plains = 'plains';
    case Forest = 'forest';
    case Swamp = 'swamp';
    case Hills = 'hills';
    case Mountain = 'mountain';
    case Water = 'water';
    case Underground = 'underground';

    public function moveCost(): float
    {
        return match($this) {
            Terrain::Inside => 1,
            Terrain::Town => 1,
            Terrain::City => 1,
            Terrain::Road => 1.5,
            Terrain::Plains => 2,
            Terrain::Forest => 3,
            Terrain::Swamp => 3,
            Terrain::Hills => 4,
            Terrain::Mountain => 5,
            Terrain::Water => 6,
            Terrain::Underground => 3
        };
    }
}
