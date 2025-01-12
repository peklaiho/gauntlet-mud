<?php
/**
 * Gauntlet MUD - Template for rooms
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Template;

use Gauntlet\ExtraDesc;
use Gauntlet\Enum\Direction;
use Gauntlet\Enum\Terrain;
use Gauntlet\Trait\AmbientMessages;

class RoomTemplate extends BaseTemplate
{
    protected array $extraDescs = [];
    protected array $exits = [];
    protected Terrain $terrain = Terrain::City;

    use AmbientMessages;

    public function addExtraDesc(ExtraDesc $extra): void
    {
        $this->extraDescs[] = $extra;
    }

    public function getExtraDesc(string $search): ?string
    {
        foreach ($this->extraDescs as $desc) {
            if ($desc->hasKeyword($search)) {
                return $desc->getDescription();
            }
        }

        return null;
    }

    public function getExit(Direction $dir): ?RoomExitTemplate
    {
        return $this->exits[$dir->value] ?? null;
    }

    public function getExits(): array
    {
        return $this->exits;
    }

    public function getTerrain(): Terrain
    {
        return $this->terrain;
    }

    public function setExit(Direction $dir, RoomExitTemplate $val): void
    {
        $this->exits[$dir->value] = $val;
    }

    public function setTerrain(Terrain $val): void
    {
        $this->terrain = $val;
    }
}
