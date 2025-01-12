<?php
/**
 * Gauntlet MUD - Global lists
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

class Lists
{
    protected Collection $descriptors;
    protected Collection $factions;
    protected Collection $items;
    protected Collection $itemTemplates;
    protected Collection $living;
    protected Collection $monsterTemplates;
    protected Collection $rooms;
    protected Collection $roomTemplates;
    protected Collection $shops;
    protected Collection $zones;
    protected Collection $zoneTemplates;
    protected Collection $groups;

    public function __construct() {
        $this->descriptors = new Collection();
        $this->factions = new Collection();
        $this->items = new Collection();
        $this->itemTemplates = new Collection();
        $this->living = new Collection();
        $this->monsterTemplates = new Collection();
        $this->rooms = new Collection();
        $this->roomTemplates = new Collection();
        $this->shops = new Collection();
        $this->zones = new Collection();
        $this->zoneTemplates = new Collection();
        $this->groups = new Collection();
    }

    public function getDescriptors(): Collection
    {
        return $this->descriptors;
    }

    public function getFactions(): Collection
    {
        return $this->factions;
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function getItemTemplates(): Collection
    {
        return $this->itemTemplates;
    }

    public function getLiving(): Collection
    {
        return $this->living;
    }

    public function getMonsterTemplates(): Collection
    {
        return $this->monsterTemplates;
    }

    public function getRooms(): Collection
    {
        return $this->rooms;
    }

    public function getRoomTemplates(): Collection
    {
        return $this->roomTemplates;
    }

    public function getShops(): Collection
    {
        return $this->shops;
    }

    public function getZones(): Collection
    {
        return $this->zones;
    }

    public function getZoneTemplates(): Collection
    {
        return $this->zoneTemplates;
    }

    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function findPlayer(string $name): ?Player
    {
        foreach ($this->getLiving()->getAll() as $living) {
            if ($living->isPlayer() && $living->getName() == $name) {
                return $living;
            }
        }

        return null;
    }

    public function findRoomsForRoomId(int $roomId): array
    {
        $result = [];

        foreach ($this->getZones()->getAll() as $zone) {
            $room = $zone->getRooms()->get($roomId);
            if ($room) {
                $result[] = $room;
            }
        }

        return $result;
    }
}
