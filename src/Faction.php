<?php
/**
 * Gauntlet MUD - Faction
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Enum\Fondness;

class Faction extends BaseObject
{
    protected string $id;
    protected string $name;
    protected array $fondness = [];

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFondness(Faction $otherFaction): Fondness
    {
        return $this->fondness[$otherFaction->getId()] ?? Fondness::Neutral;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setFondness(string $otherFactionId, Fondness $value): void
    {
        $this->fondness[$otherFactionId] = $value;
    }

    #[\Override]
    public function getTechnicalName(): string
    {
        return "Faction<{$this->id}>";
    }
}
