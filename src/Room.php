<?php
/**
 * Gauntlet MUD - Room instance
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Enum\Direction;
use Gauntlet\Enum\PartOfDay;
use Gauntlet\Enum\RoomFlag;
use Gauntlet\Enum\ScriptType;
use Gauntlet\Enum\Terrain;
use Gauntlet\Template\RoomTemplate;
use Gauntlet\Trait\MagicNumber;
use Gauntlet\Util\GameTime;

class Room extends BaseObject
{
    protected array $exits = [];
    protected Collection $items;
    protected Collection $living;

    use MagicNumber;

    public function __construct(
        protected Zone $zone,
        protected RoomTemplate $template,
        int $magicNum
    ) {
        $this->setMagicNumber($magicNum);

        $this->items = new Collection();
        $this->living = new Collection();
    }

    public function isDark(): bool
    {
        // Flagged rooms permanently dark or lighted
        if ($this->template->hasFlag(RoomFlag::Light)) {
            return false;
        } elseif ($this->template->hasFlag(RoomFlag::Dark)) {
            return true;
        }

        // Towns, cities and inside buildings are always lit by default,
        // underground is always dark.
        if ($this->template->getTerrain() == Terrain::Inside ||
            $this->template->getTerrain() == Terrain::Town ||
            $this->template->getTerrain() == Terrain::City) {
            return false;
        } elseif ($this->template->getTerrain() == Terrain::Underground) {
            return true;
        }

        // For other terrain it is dark at night
        $partOfDay = GameTime::now()->getPartOfDay();
        return $partOfDay == PartOfDay::Night;
    }

    public function getExit(Direction $dir): ?RoomExit
    {
        return $this->exits[$dir->value] ?? null;
    }

    public function getExits(?Living $passableBy = null): array
    {
        if ($passableBy) {
            // array_filter preserves keys
            return array_filter($this->exits, fn ($e) => $e->isPassable($passableBy));
        } else {
            return $this->exits;
        }
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function getLiving(): Collection
    {
        return $this->living;
    }

    public function getTemplate(): RoomTemplate
    {
        return $this->template;
    }

    public function getZone(): Zone
    {
        return $this->zone;
    }

    public function setExit(Direction $dir, ?RoomExit $val): void
    {
        if ($val === null) {
            unset($this->exits[$dir->value]);
        } else {
            $this->exits[$dir->value] = $val;
        }
    }

    // Override script getters: Read from template

    #[\Override]
    public function getScript(ScriptType $type): ?string
    {
        return $this->template->getScript($type);
    }

    #[\Override]
    public function getScripts(): array
    {
        return $this->template->getScripts();
    }

    #[\Override]
    public function getTechnicalName(): string
    {
        return "Room<{$this->id}:{$this->getMagicNumber()}>";
    }
}
