<?php
/**
 * Gauntlet MUD - Functions to create instances of zones, rooms, items and monsters
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Data\IFactionRepository;
use Gauntlet\Data\IItemTemplateRepository;
use Gauntlet\Data\IMonsterTemplateRepository;
use Gauntlet\Data\IRoomRepository;
use Gauntlet\Data\IShopRepository;
use Gauntlet\Data\IZoneRepository;
use Gauntlet\Enum\Direction;
use Gauntlet\Enum\EqSlot;
use Gauntlet\Enum\ScriptType;
use Gauntlet\Enum\ZoneType;
use Gauntlet\Template\ItemTemplate;
use Gauntlet\Template\MonsterTemplate;
use Gauntlet\Template\ZoneTemplate;
use Gauntlet\Util\Config;
use Gauntlet\Util\Lisp;
use Gauntlet\Util\Log;

class World
{
    protected int $monsterNum;
    protected int $itemNum;
    protected int $roomNum;
    protected int $roomExitNum;
    protected int $zoneNum;

    public function __construct(
        protected IRoomRepository $roomRepo,
        protected IItemTemplateRepository $itemRepo,
        protected IMonsterTemplateRepository $monsterRepo,
        protected IShopRepository $shopRepo,
        protected IZoneRepository $zoneRepo,
        protected IFactionRepository $factionRepo,
        protected GroupHandler $groupHandler,
        protected Lists $lists
    ) {

    }

    public function extractItem(Item $item): void
    {
        $item->invalidate();

        // Extract contents
        while (!$item->getContents()->empty()) {
            $this->extractItem($item->getContents()->first());
        }

        $this->detachItem($item);
        $this->lists->getItems()->remove($item);

        $item->getTemplate()->decCount();
    }

    public function extractLiving(Living $living): void
    {
        $living->invalidate();

        // Extract inventory
        while (!$living->getInventory()->empty()) {
            $this->extractItem($living->getInventory()->first());
        }

        // Extract equipment
        while (!$living->getEquipment()->empty()) {
            $this->extractItem($living->getEquipment()->first());
        }

        if ($living->getGroup()) {
            $this->groupHandler->leave($living);
        }

        $this->detachLiving($living);
        $this->lists->getLiving()->remove($living);

        if ($living->isMonster()) {
            $living->getTemplate()->decCount();
        }
    }

    public function extractZone(Zone $zone): void
    {
        foreach ($zone->getRooms()->getAll() as $room) {
            foreach ($room->getLiving()->getAll() as $living) {
                $this->extractLiving($living);
            }

            foreach ($room->getItems()->getAll() as $item) {
                $this->extractItem($item);
            }

            $this->lists->getRooms()->remove($room);
        }

        $this->lists->getZones()->remove($zone);
        $zone->getTemplate()->decCount();
    }

    public function getStartingRoom(Player $player): Room
    {
        $startZoneId = Config::startingZoneId();
        $startRoomId = Config::startingRoomId();

        $startRoom = null;

        $zoneTemplate = $this->lists->getZoneTemplates()->get($startZoneId);
        if (!$zoneTemplate) {
            throw new \RuntimeException("Unable to find starting zone $startZoneId.");
        }

        if ($zoneTemplate->getType() == ZoneType::Static) {
            // Static zone, should exist already
            foreach ($this->lists->getZones()->getAll() as $zone) {
                if ($zone->getTemplate()->getId() == $startZoneId) {
                    $startRoom = $zone->getRooms()->get($startRoomId);
                    break;
                }
            }
        } else {
            // Dynamic zone
            $startZone = null;

            // Find existing zone first
            foreach ($this->lists->getZones()->getAll() as $zone) {
                if ($zone->getTemplate()->getId() == $startZoneId &&
                    $zone->getOwner() == $player->getTechnicalName()) {
                    $startZone = $zone;
                    break;
                }
            }

            // Not found, create new
            if (!$startZone) {
                $startZone = $this->loadZone($zoneTemplate, $player->getTechnicalName());
            }

            $startRoom = $startZone->getRooms()->get($startRoomId);
        }

        if (!$startRoom) {
            throw new \RuntimeException("Unable to find starting room $startRoomId in zone $startZoneId.");
        }

        return $startRoom;
    }

    public function deleteTemplates(): void
    {
        $this->lists->getItemTemplates()->clear();
        $this->lists->getMonsterTemplates()->clear();
        $this->lists->getRoomTemplates()->clear();
        $this->lists->getShops()->clear();
        $this->lists->getZoneTemplates()->clear();
        $this->lists->getFactions()->clear();
    }

    public function initialize(): void
    {
        // Unique number for monsters, items, rooms and zones
        $this->monsterNum = 1;
        $this->itemNum = 1;
        $this->roomNum = 1;
        $this->roomExitNum = 1;
        $this->zoneNum = 1;

        $this->itemRepo->readInto($this->lists->getItemTemplates());
        $this->monsterRepo->readInto($this->lists->getMonsterTemplates());
        $this->roomRepo->readInto($this->lists->getRoomTemplates());
        $this->shopRepo->readInto($this->lists->getShops());
        $this->zoneRepo->readInto($this->lists->getZoneTemplates());
        $this->factionRepo->readInto($this->lists->getFactions());

        $this->prepareZones();
        $this->validateShops();
        $this->validateFactions();
    }

    public function itemToContainer(Item $item, Item $container): void
    {
        $this->detachItem($item);

        $container->getContents()->add($item);
        $item->setContainer($container);
    }

    public function itemToEquipment(Item $item, Living $living, EqSlot $slot): void
    {
        if ($living->getEqInSlot($slot)) {
            throw new \RuntimeException('Attempt to equip ' . $item->getTechnicalName() . ' in already equipped slot ' . $slot->value . ' on ' . $living->getTechnicalName());
        }

        $this->detachItem($item);

        $living->getEquipment()->set($slot->value, $item);
        $item->setWearer($living);
    }

    public function itemToInventory(Item $item, Living $living): void
    {
        $this->detachItem($item);

        $living->getInventory()->add($item);
        $item->setCarrier($living);
    }

    public function itemToRoom(Item $item, Room $room): void
    {
        $this->detachItem($item);

        $room->getItems()->add($item);
        $item->setRoom($room);
    }

    public function livingToRoom(Living $living, Room $room): void
    {
        $this->detachLiving($living);

        $room->getLiving()->add($living);
        $living->setRoom($room);

        // Run Lisp script
        $script = $room->getScript(ScriptType::Entry);
        if ($script) {
            $data = [
                'living' => $living
            ];
            Lisp::evalWithData($room, $script, $data);
        }
    }

    public function loadItemToContainer(ItemTemplate $template, Item $container): Item
    {
        $item = $this->loadItem($template);
        $this->itemToContainer($item, $container);
        $item->runInitScript();
        return $item;
    }

    public function loadItemToEquipment(ItemTemplate $template, Living $living, EqSlot $slot): Item
    {
        $item = $this->loadItem($template);
        $this->itemToEquipment($item, $living, $slot);
        $item->runInitScript();
        return $item;
    }

    public function loadItemToInventory(ItemTemplate $template, Living $living): Item
    {
        $item = $this->loadItem($template);
        $this->itemToInventory($item, $living);
        $item->runInitScript();
        return $item;
    }

    public function loadItemToRoom(ItemTemplate $template, Room $room): Item
    {
        $item = $this->loadItem($template);
        $this->itemToRoom($item, $room);
        $item->runInitScript();
        return $item;
    }

    public function loadMonster(MonsterTemplate $template, Room $room): Monster
    {
        $template->incCount();
        $monster = new Monster($template, $this->monsterNum++);
        $this->lists->getLiving()->add($monster);
        $this->livingToRoom($monster, $room);
        $monster->runInitScript();
        return $monster;
    }

    public function loadZone(ZoneTemplate $template, ?string $owner = null): Zone
    {
        $zone = $this->loadZonePart1($template, $owner);
        $this->loadZonePart2($zone);
        $this->loadZonePart3($zone);
        return $zone;
    }

    // Load zone part 1: Create instances for the zone and rooms
    private function loadZonePart1(ZoneTemplate $template, ?string $owner = null): Zone
    {
        Log::debug("Creating instance of zone {$template->getId()}: {$template->getName()}");

        $template->incCount();
        $zone = new Zone($template, $this->zoneNum++);
        $zone->setOwner($owner);
        $this->lists->getZones()->add($zone);
        $zone->runInitScript();

        // Create rooms
        foreach ($template->getRoomTemplates() as $roomTemplate) {
            $room = new Room($zone, $roomTemplate, $this->roomNum++);
            $this->lists->getRooms()->add($room);
            $zone->getRooms()->set($roomTemplate->getId(), $room);
            $room->runInitScript();
        }

        return $zone;
    }

    // Load zone part 2: Create exits
    private function loadZonePart2(Zone $zone): void
    {
        // Connect outgoing exits (thisZone -> otherZone)
        foreach ($zone->getRooms()->getAll() as $room) {
            foreach ($room->getTemplate()->getExits() as $dirName => $exitTemplate) {
                // First look in same zone
                $target = $zone->getRooms()->get($exitTemplate->getRoomId());

                // Secondly find all room instances with the given roomId
                if (!$target) {
                    $candidates = $this->lists->findRoomsForRoomId($exitTemplate->getRoomId());

                    foreach ($candidates as $c) {
                        // For now connect only to static zones
                        if ($c->getZone()->getTemplate()->getType() == ZoneType::Static) {
                            $target = $c;
                            break;
                        }
                    }
                }

                if ($target) {
                    $dir = Direction::from($dirName);
                    $exit = new RoomExit($exitTemplate, $room, $dir, $target, $this->roomExitNum++);
                    $room->setExit($dir, $exit);
                    $exit->runInitScript();
                }
            }
        }

        // Connect incoming exits (otherZone -> thisZone)
        foreach ($this->lists->getZones()->getAll() as $otherZone) {
            // Skip same zone and other dynamic zones
            if ($zone === $otherZone || $otherZone->getTemplate()->getType() == ZoneType::Dynamic) {
                continue;
            }

            foreach ($otherZone->getRooms()->getAll() as $room) {
                foreach ($room->getTemplate()->getExits() as $dirName => $exitTemplate) {
                    $dir = Direction::from($dirName);

                    // Skip exits that lead to other zones
                    if (!$zone->getTemplate()->ownsRoomId($exitTemplate->getRoomId())) {
                        continue;
                    }

                    // Skip if exit instance already exists
                    if ($room->getExit($dir)) {
                        continue;
                    }

                    $target = $otherZone->getRooms()->get($exitTemplate->getRoomId());

                    if ($target) {
                        $exit = new RoomExit($exitTemplate, $room, $dir, $target, $this->roomExitNum++);
                        $room->setExit($dir, $exit);
                        $exit->runInitScript();
                    }
                }
            }
        }
    }

    // Load zone part 3: Reset zone (create monsters and items)
    private function loadZonePart3(Zone $zone): void
    {
        $zoneReset = new ZoneReset($this, $this->lists);
        $zoneReset->reset($zone, true);
    }

    public function detachLiving(Living $living): void
    {
        if ($living->getTarget()) {
            $living->setTarget(null);
        }

        if ($living->getRoom()) {
            foreach ($living->getRoom()->getLiving()->getAll() as $other) {
                if ($other->getTarget() === $living) {
                    $other->setTarget(null);
                }
            }

            $living->getRoom()->getLiving()->remove($living);
            $living->setRoom(null);
        }
    }

    private function detachItem(Item $item): void
    {
        if ($item->getRoom()) {
            $item->getRoom()->getItems()->remove($item);
            $item->setRoom(null);
        }
        if ($item->getCarrier()) {
            $item->getCarrier()->getInventory()->remove($item);
            $item->setCarrier(null);
        }
        if ($item->getContainer()) {
            $item->getContainer()->getContents()->remove($item);
            $item->setContainer(null);
        }
        if ($item->getWearer()) {
            $item->getWearer()->getEquipment()->remove($item);
            $item->setWearer(null);
        }
    }

    private function loadItem(ItemTemplate $template): Item
    {
        $template->incCount();
        $item = new Item($template, $this->itemNum++);
        $this->lists->getItems()->add($item);
        return $item;
    }

    private function prepareZones(): void
    {
        Log::info("Assign rooms to zones.");

        foreach ($this->lists->getRoomTemplates()->getAll() as $roomTemplate) {
            $found = false;

            foreach ($this->lists->getZoneTemplates()->getAll() as $zoneTemplate) {
                if ($zoneTemplate->ownsRoomId($roomTemplate->getId())) {
                    $zoneTemplate->addRoomTemplate($roomTemplate);
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                Log::error("Room {$roomTemplate->getId()} does not belong to any zone.");
            }
        }

        Log::info("Create static zones.");

        $zones = [];

        foreach ($this->lists->getZoneTemplates()->getAll() as $zoneTemplate) {
            if ($zoneTemplate->getType() == ZoneType::Static) {
                $zones[] = $this->loadZonePart1($zoneTemplate);
            }
        }

        foreach ($zones as $zone) {
            $this->loadZonePart2($zone);
        }

        foreach ($zones as $zone) {
            $this->loadZonePart3($zone);
        }
    }

    private function validateShops(): void
    {
        foreach ($this->lists->getShops()->getAll() as $shop) {
            $validItemIds = [];
            foreach ($shop->getItemIds() as $itemId) {
                if ($this->lists->getItemTemplates()->get($itemId)) {
                    $validItemIds[] = $itemId;
                } else {
                    Log::error("Item $itemId does not exists for shop in room {$shop->getRoomId()}.");
                }
            }
            $shop->setItemIds($validItemIds);
        }
    }

    private function validateFactions(): void
    {
        foreach ($this->lists->getMonsterTemplates()->getAll() as $monster) {
            if ($monster->getFactionId()) {
                $faction = $this->lists->getFactions()->get($monster->getFactionId());
                if ($faction) {
                    $monster->setFaction($faction);
                } else {
                    Log::error("Monster {$monster->getId()} has invalid faction: " . $monster->getFactionId());
                }
            }
        }
    }
}
