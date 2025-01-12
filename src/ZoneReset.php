<?php
/**
 * Gauntlet MUD - Zone reset handler
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Enum\Direction;
use Gauntlet\Enum\EqSlot;
use Gauntlet\Template\BaseTemplate;
use Gauntlet\Template\ItemTemplate;
use Gauntlet\Template\MonsterTemplate;
use Gauntlet\Util\Log;
use Gauntlet\Util\Random;

class ZoneReset
{
    public function __construct(
        protected World $world,
        protected Lists $lists
    ) {

    }

    public function reset(Zone $zone, bool $init): void
    {
        Log::debug("Reset zone {$zone->getTemplate()->getId()}: {$zone->getTemplate()->getName()}");
        $zone->setResetTime();

        foreach ($zone->getTemplate()->getOps() as $op) {
            $this->execOp($zone, $op, null);
        }
    }

    private function execOp(Zone $zone, ZoneOp $op, $parentResult): void
    {
        $raw = $op->getRaw();
        $result = null;

        switch ($op->getType()) {
            case 'M':
                // Load monster in room
                // <monsterId> <roomId> <limit>

                $template = $this->findMonsterTemplate($op->get(1), $op->get(3, 0), $raw);
                $room = $this->findRoom($op->get(2, '*'), $zone, $raw);

                if (!$template || !$room) {
                    return;
                }

                // Log::debug("ZoneOp: load monster {$template->getId()} in room {$room->getTemplate()->getId()}");
                $result = $this->world->loadMonster($template, $room);
                break;

            case 'O':
                // Load object in room
                // <itemId> <roomId> <limit>

                $template = $this->findItemTemplate($op->get(1), $op->get(3, 0), $raw);
                $room = $this->findRoom($op->get(2, '*'), $zone, $raw);

                if (!$template || !$room) {
                    return;
                }

                // Log::debug("ZoneOp: load item {$template->getId()} in room {$room->getTemplate()->getId()}");
                $result = $this->world->loadItemToRoom($template, $room);
                break;

            case 'G':
                // Give item to monster
                // <itemId> <limit>

                if (!$parentResult || !($parentResult instanceof Monster)) {
                    Log::error("ZoneOp: G operation that does not have M as parent: $raw");
                    return;
                }

                $template = $this->findItemTemplate($op->get(1), $op->get(2, 0), $raw);
                if (!$template) {
                    return;
                }

                // Log::debug("ZoneOp: give item {$template->getId()} to monster {$parentResult->getTemplate()->getId()}");
                $result = $this->world->loadItemToInventory($template, $parentResult);
                break;

            case 'E':
                // Equip monster
                // <itemId> <slot> <limit>

                if (!$parentResult || !($parentResult instanceof Monster)) {
                    Log::error("ZoneOp: E operation that does not have M as parent: $raw");
                    return;
                }

                $template = $this->findItemTemplate($op->get(1), $op->get(3, 0), $raw);
                if (!$template) {
                    return;
                }

                $slot = EqSlot::tryFrom($op->get(2));

                if (!$slot) {
                    Log::error("ZoneOp: E operation has invalid equipment slot: " . $op->get(2));
                    return;
                }

                if ($parentResult->getEqInSlot($slot)) {
                    // Log::debug("ZoneOp: skip equipping item {$template->getId()} on monster {$parentResult->getTemplate()->getId()} because slot is used");
                    return;
                }

                // Log::debug("ZoneOp: equip item {$template->getId()} on monster {$parentResult->getTemplate()->getId()}");
                $result = $this->world->loadItemToEquipment($template, $parentResult, $slot);
                break;

            case 'P':
                // Put object inside object
                // <itemId> <limit>

                if (!$parentResult || !($parentResult instanceof Item)) {
                    Log::error("ZoneOp: P operation that does not have O, G, E or P as parent: $raw");
                    return;
                }

                $template = $this->findItemTemplate($op->get(1), $op->get(2, 0), $raw);
                if (!$template) {
                    return;
                }

                // Log::debug("ZoneOp: put item {$template->getId()} inside container {$parentResult->getTemplate()->getId()}");
                $result = $this->world->loadItemToContainer($template, $parentResult);
                break;

            case 'D':
                // Set door state
                // <roomId> <dir> <state>

                if ($op->count() < 4) {
                    Log::error("ZoneOp: D operation without required arguments: $raw");
                    return;
                }

                $room = $this->findRoom($op->get(1), $zone, $raw);
                if (!$room) {
                    return;
                }

                $dir = Direction::tryFrom($op->get(2));
                if (!$dir) {
                    Log::error("ZoneOp: D operation with invalid dir: $raw");
                    return;
                }

                $exit = $room->getExit($dir);
                if (!$exit) {
                    Log::error("ZoneOp: D operation with invalid exit {$op->get(2)}: $raw");
                    return;
                }

                if ($op->get(3) == 'open') {
                    $this->setExitState($exit, $dir, false, false);
                } elseif ($op->get(3) == 'closed') {
                    $this->setExitState($exit, $dir, true, false);
                } elseif ($op->get(3) == 'locked') {
                    $this->setExitState($exit, $dir, true, true);
                } else {
                    Log::error("ZoneOp: D operation with invalid state {$op->get(3)}: $raw");
                    return;
                }

                break;

            case 'R':
                // Remove object from room
                // <itemId> <roomId>

                $room = $this->findRoom($op->get(2), $zone, $raw);
                if (!$room) {
                    return;
                }

                $item = null;
                foreach ($room->getItems()->getAll() as $i) {
                    if ($i->getTemplate()->getId() == $op->get(1)) {
                        $item = $i;
                        break;
                    }
                }
                if (!$item) {
                    return;
                }

                // Log::debug("ZoneOp: remove item {$item->getTemplate()->getId()} from room {$room->getTemplate()->getId()}");
                $this->world->extractItem($item);
                break;

            case 'A':
                // Display a message in the room
                // <roomId> <message>

                $room = $this->findRoom($op->get(1), $zone, $raw);
                if (!$room) {
                    return;
                }

                $message = trim(substr($raw, strpos($raw, $op->get(1)) + strlen($op->get(1))));

                foreach ($room->getLiving()->getAll() as $target) {
                    if ($target->isPlayer()) {
                        $target->outln($message);
                    }
                }
                break;
        }

        // Execute any child ops
        foreach ($op->getChildren() as $child) {
            $this->execOp($zone, $child, $result);
        }
    }

    private function findItemTemplate(string $itemId, int $limit, string $raw): ?ItemTemplate
    {
        return $this->findTemplate('item', $this->lists->getItemTemplates(), $itemId, $limit, $raw);
    }

    private function findMonsterTemplate(string $monsterId, int $limit, string $raw): ?MonsterTemplate
    {
        return $this->findTemplate('monster', $this->lists->getMonsterTemplates(), $monsterId, $limit, $raw);
    }

    private function findTemplate(string $type, Collection $list, string $id, int $limit, string $raw): ?BaseTemplate
    {
        if (strpos($id, '-') !== false) {
            $template = $this->randFromRange($list, explode('-', $id));
        } else {
            if (strpos($id, ',') !== false) {
                $id = Random::fromArray(explode(',', $id));
            }

            $template = $list->get($id);
        }

        if (!$template) {
            Log::error("ZoneOp: Unable to find $type $id: $raw");
            return null;
        }

        // Check limit
        $count = $template->getCount();
        if ($limit > 0 && $count >= $limit) {
            // Log::debug("ZoneOp: skip loading $type $id because of limit $limit (count $count): $raw");
            return null;
        }

        return $template;
    }

    private function findRoom(string $roomId, Zone $zone, string $raw): ?Room
    {
        if ($roomId == '*') {
            $room = $this->randFromRange($zone->getRooms(), null);
        } elseif (strpos($roomId, '-') !== false) {
            $room = $this->randFromRange($zone->getRooms(), explode('-', $roomId));
        } else {
            $room = $zone->getRooms()->get($roomId);
        }

        if (!$room) {
            Log::error("ZoneOp: Unable to find room $roomId: $raw");
        }

        return $room;
    }

    private function randFromRange(Collection $list, ?array $range): BaseTemplate|Room|null
    {
        $candidates = [];

        foreach ($list->getAll() as $a) {
            if ($range == null || ($a->getTemplate()->getId() >= $range[0] && $a->getTemplate()->getId() <= $range[1])) {
                $candidates[] = $a;
            }
        }

        return Random::fromArray($candidates);
    }

    private function setExitState(RoomExit $exit, Direction $dir, bool $closed, bool $locked): void
    {
        $exit->setClosed($closed);
        $exit->setLocked($locked);

        $oppRoom = $exit->getTo();
        $oppExit = $oppRoom->getExit($dir->opposite());

        if ($oppExit) {
            $oppExit->setClosed($closed);
            $oppExit->setLocked($locked);
        }
    }
}
