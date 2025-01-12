<?php
/**
 * Gauntlet MUD - Render rooms, items and monsters
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Enum\Direction;
use Gauntlet\Enum\EqSlot;
use Gauntlet\Enum\ItemFlag;
use Gauntlet\Util\ColorPref;
use Gauntlet\Util\Preferences;

class Renderer
{
    public function renderCondition(Player $player, Living $target): string
    {
        $percentage = ($target->getHealth() / $target->getMaxHealth()) * 100;

        return match (true) {
            $percentage >= 100 => 'is in perfect health.',
            $percentage >= 80 => 'has a few scratches.',
            $percentage >= 60 => 'has some big nasty wounds.',
            $percentage >= 40 => 'is hurting really bad.',
            $percentage >= 20 => 'is bleeding awfully from open wounds.',
            $percentage > 0 => 'is barely hanging on to life.',
            default => 'is already dead!',
        };
    }

    public function renderEquipment(Player $player, Collection $list, bool $showEmpty, bool $showInvis): array
    {
        $output = [];

        foreach (EqSlot::list() as $slot) {
            $eq = $list->get($slot->value);

            if ($showEmpty || ($eq && ($player->canSeeItem($eq) || $showInvis))) {
                $line = $slot->renderString();

                if ($eq) {
                    $line .= '   ';
                    if ($player->canSeeItem($eq)) {
                        $line .= $eq->getTemplate()->getAName();
                    } else {
                        $line .= 'something';
                    }
                }

                $output[] = $line;
            }
        }

        return $output;
    }

    public function renderItems(Player $player, Collection $list): bool
    {
        $output = false;

        $items = $this->getVisibleItems($list, $player);
        $groups = $this->groupItems($items);

        foreach ($groups as $objs) {
            $template = $objs[0]->getTemplate();
            $player->out($template->getAName(count($objs)));

            if (count($objs) == 1) {
                $contentCount = count($this->getVisibleItems($objs[0]->getContents(), $player));
                if ($contentCount > 0) {
                    $s = $contentCount != 1 ? 's' : '';
                    $player->out(" (contains $contentCount item$s)");
                }
            }

            $player->outln();
            $output = true;
        }

        return $output;
    }

    public function renderRoom(Player $player, Room $room, bool $checkBriefMode = false): void
    {
        // If the player had previous output, add extra linebreak if not in compact mode
        if ($player->getDescriptor() &&
            $player->getDescriptor()->getOutput() &&
            !$player->getPreference(Preferences::COMPACT)) {
            $player->outln();
        }

        if (!$player->canSeeRoom()) {
            $player->outln(MESSAGE_DARK);
            return;
        }

        // Name
        $roomName = $room->getTemplate()->getName();
        if ($player->getAdminLevel()) {
            $roomId = $room->getTemplate()->getId() . ':' . $room->getZone()->getMagicNumber();
            $roomName = '[' . $roomId . '] ' . $roomName . ' (' .
                ucfirst($room->getTemplate()->getTerrain()->value) . ')';
            if ($room->getTemplate()->getFlags()) {
                $roomName .= ' [' . $room->getTemplate()->renderFlags() . ']';
            }
        }
        $player->outln($player->colorize($roomName, ColorPref::ROOMNAME));

        // Support brief mode
        $showRoomDesc = true;
        if ($checkBriefMode && $player->getPreference(Preferences::BRIEF)) {
            $showRoomDesc = false;
        }

        // Room desc (if one exists)
        if ($showRoomDesc && $room->getTemplate()->getLongDesc()) {
            $player->outpr($player->colorize($player->highlight($room->getTemplate()->getLongDesc(), ColorPref::ROOMDESC), ColorPref::ROOMDESC));
        }

        // Exits
        $exits = [];
        foreach ($player->getRoom()->getExits($player) as $dirName => $notUsed) {
            $exits[] = ucfirst(Direction::from($dirName)->name());
        }

        if ($exits) {
            $exitString = 'Exits: ' . implode(', ', $exits);
        } else {
            $exitString = 'Exits: None!';
        }
        $player->outln($player->colorize($exitString, ColorPref::ROOMEXIT));

        // Items
        $items = $this->getVisibleItems($room->getItems(), $player);
        $itemDesc = $this->formatItems($items);
        foreach ($itemDesc as $desc) {
            $player->outpr($player->colorize($desc, ColorPref::ROOMOBJ));
        }

        // Monsters and players
        $monsters = [];
        $players = [];

        foreach ($room->getLiving()->getAll() as $living) {
            if (!$player->canSee($living) || $player === $living) {
                continue;
            }

            if ($living->isMonster()) {
                $monsters[] = $living;
            } else {
                $players[] = $living;
            }
        }

        $monsterDesc = $this->formatMonsters($monsters, $player);
        foreach ($monsterDesc as $desc) {
            $player->outpr($player->colorize($desc, ColorPref::ROOMNPC));
        }

        $playerDesc = $this->formatPlayers($players, $player);
        foreach ($playerDesc as $desc) {
            $player->outpr($player->colorize($desc, ColorPref::ROOMPLAYER));
        }
    }

    private function formatItems(array $list): array
    {
        $output = [];
        $generic = [];
        $genericCount = 0;

        $groups = $this->groupItems($list);

        foreach ($groups as $items) {
            $template = $items[0]->getTemplate();

            if (count($items) == 1 && $template->getShortDesc()) {
                $output[] = $template->getShortDesc();
            } else {
                $generic[] = $template->getAName(count($items));
                $genericCount += count($items);

                // Always treat as plural if flagged as such
                if ($template->hasFlag(ItemFlag::Plural)) {
                    $genericCount++;
                }
            }
        }

        if ($genericCount > 0) {
            $output[] = $this->listToString($generic, $genericCount > 1) . '.';
        }

        return $output;
    }

    private function formatMonsters(array $list, Player $player): array
    {
        $output = [];
        $generic = [];
        $genericCount = 0;

        $groups = $this->groupLiving($list);

        foreach ($groups as $monsters) {
            $template = $monsters[0]->getTemplate();
            $target = $monsters[0]->getTarget();

            if ($target) {
                $output[] = $this->fightDesc($monsters, $player);
            } else {
                if (count($monsters) == 1 && $template->getShortDesc()) {
                    $output[] = $template->getShortDesc();
                } else {
                    $generic[] = $template->getAName(count($monsters));
                    $genericCount += count($monsters);
                }
            }
        }

        if ($genericCount > 0) {
            $output[] = $this->listToString($generic, $genericCount > 1) . '.';
        }

        return $output;
    }

    private function formatPlayers(array $list, Player $player): array
    {
        $output = [];

        $groups = $this->groupLiving($list);

        foreach ($groups as $players) {
            $target = $players[0]->getTarget();

            $names = array_map(fn ($p) => $p->getNameWithState(), $players);
            $txt = $this->listToString($names, count($names) > 1);

            if ($target) {
                $txt .= ', fighting ' . $this->targetName($target, $player);
            } else {
                $txt .= '.';
            }

            $output[] = $txt;
        }

        return $output;
    }

    private function getVisibleItems(Collection $list, Player $player): array
    {
        $items = [];

        foreach ($list->getAll() as $item) {
            if (!$player->canSeeItem($item)) {
                continue;
            }

            $items[] = $item;
        }

        return $items;
    }

    private function groupItems(array $list): array
    {
        $groups = [];

        foreach ($list as $obj) {
            $id = $obj->getTemplate()->getId();
            $groups[$id][] = $obj;
        }

        return $groups;
    }

    private function groupLiving(array $list): array
    {
        $groups = [];

        foreach ($list as $living) {
            $key = $this->keyForGroup($living);
            $groups[$key][] = $living;
        }

        return $groups;
    }

    private function keyForGroup(Living $living): string
    {
        if ($living->isPlayer()) {
            if ($living->getTarget()) {
                return $living->getTarget()->getTechnicalName();
            }

            return 'none';
        } else {
            $id = $living->getTemplate()->getId();

            $target = 'none';
            if ($living->getTarget()) {
                $target = $living->getTarget()->getTechnicalName();
            }

            return "$id/$target";
        }
    }

    private function fightDesc(array $attackers, Player $player): string
    {
        $target = $attackers[0]->getTarget();

        $txt = $attackers[0]->getTemplate()->getAName(count($attackers));

        if (count($attackers) == 1) {
            $txt .= ' is here, fighting ';
        } else {
            $txt .= ' are here, fighting ';
        }

        $txt .= $this->targetName($target, $player);

        return ucfirst($txt);
    }

    private function targetName(Living $target, Player $player): string
    {
        if ($player === $target) {
            return 'you!';
        } elseif ($player->canSee($target)) {
            if ($target->isPlayer()) {
                return $target->getName() . '.';
            } else {
                return $target->getTemplate()->getAName() . '.';
            }
        } else {
            return 'someone.';
        }
    }

    private function listToString(array $items, bool $plural): string
    {
        $txt = implode(', ', array_slice($items, 0, count($items) - 1));

        if (count($items) > 1) {
            $txt .= ' and ';
        }

        $txt .= $items[count($items) - 1];

        $txt .= $plural ? ' are here' : ' is here';

        return ucfirst($txt);
    }
}
