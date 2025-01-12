<?php
/**
 * Gauntlet MUD - Template for monsters
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Template;

use Gauntlet\AmbientMessage;
use Gauntlet\Faction;
use Gauntlet\Trait\AmbientMessages;
use Gauntlet\Trait\AttackType;
use Gauntlet\Trait\DamageType;
use Gauntlet\Trait\Level;
use Gauntlet\Trait\SexAndSize;

class MonsterTemplate extends BaseTemplate
{
    protected int $numAttacks = 1;
    protected ?string $factionId = null;
    protected ?Faction $faction = null;
    protected array $avoidRooms = [];

    use AmbientMessages;
    use AttackType;
    use DamageType;
    use Level;
    use SexAndSize;

    public function getNumAttacks(): int
    {
        return $this->numAttacks;
    }

    public function getFaction(): ?Faction
    {
        return $this->faction;
    }

    public function getFactionId(): ?string
    {
        return $this->factionId;
    }

    public function getAvoidRooms(): array
    {
        return $this->avoidRooms;
    }

    public function setNumAttacks(int $val): void
    {
        $this->numAttacks = $val;
    }

    public function setFaction(?Faction $val): void
    {
        $this->faction = $val;
    }

    public function setFactionId(?string $val): void
    {
        $this->factionId = $val;
    }

    public function setAvoidRooms(array $val): void
    {
        $this->avoidRooms = $val;
    }
}
