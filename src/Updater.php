<?php
/**
 * Gauntlet MUD - Periodic updates to rooms, items, monsters and fights
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Enum\Direction;
use Gauntlet\Enum\ItemFlag;
use Gauntlet\Enum\MonsterFlag;
use Gauntlet\Enum\PartOfDay;
use Gauntlet\Enum\ScriptType;
use Gauntlet\Enum\Terrain;
use Gauntlet\Util\GameTime;
use Gauntlet\Util\Lisp;
use Gauntlet\Util\Log;
use Gauntlet\Util\Random;

class Updater
{
    protected ?PartOfDay $lastPartOfDay = null;

    public function __construct(
        protected World $world,
        protected ZoneReset $zoneReset,
        protected Fight $fight,
        protected Renderer $render,
        protected AmbientHandler $ambientHandler,
        protected Act $act,
        protected ActionMove $actionMove,
        protected Lists $lists
    ) {

    }

    public function tick(int $i): void
    {
        $this->updateLiving($i);
        $this->updateFights($i);
        $this->updateItems($i);
        $this->updateRooms($i);
        $this->updateZones($i);
        $this->updateTime($i);
    }

    private function updateLiving(int $i): void
    {
        foreach ($this->lists->getLiving()->getAll() as $living) {
            // All players update on same tick, monsters update on tick that depends on their magic number.
            if (($i % UPDATE_LIVING) != ($living->isPlayer() ? 0 : ($living->getMagicNumber() % UPDATE_LIVING))) {
                continue;
            }
            // Skip invalid and fighters
            if (!$living->isValidObject() || $living->getTarget()) {
                continue;
            }

            // Regenerate health
            $living->regenerate();

            // Players don't have any other actions
            if ($living->isPlayer()) {
                continue;
            }

            $actionResult = null;

            // Try script first if it exists
            $script = $living->getScript(ScriptType::Update);
            if ($script) {
                $actionResult = Lisp::eval($living, $script);
            }

            // Try movement next
            if (!$actionResult) {
                $actionResult = $this->moveMonster($living);
            }

            // Finally try ambient messages
            if (!$actionResult && $living->getTemplate()->getAmbientMessages()) {
                $this->ambientHandler->handleMonster($living);
            }
        }
    }

    private function updateFights(int $i): void
    {
        if ($i % UPDATE_FIGHTS != 0) {
            return;
        }

        $showConditions = [];

        foreach ($this->lists->getLiving()->getAll() as $living) {
            // Skip invalid and non-fighters
            if (!$living->isValidObject() || !$living->getTarget()) {
                continue;
            }

            $actionResult = null;

            if ($living->isMonster()) {
                // Try script if it exists
                $script = $living->getScript(ScriptType::Fight);
                if ($script) {
                    $actionResult = Lisp::eval($living, $script);
                }
            }

            // No action: execute normal attack
            if (!$actionResult) {
                if ($living->canSee($living->getTarget())) {
                    $this->fight->attack($living, $living->getTarget());
                    if ($living->isPlayer()) {
                        $showConditions[] = $living;
                    }
                } else {
                    if ($living->isPlayer()) {
                        $living->outln('You try to attack but are unable to see your target.');
                    }
                }
            }
        }

        // Show condition of targets to players during fights
        foreach ($showConditions as $living) {
            $target = $living->getTarget();
            if ($target) {
                $condition = $this->render->renderCondition($living, $target);
                $this->act->toChar("@T " . $condition, $living, null, $target);
            }
        }
    }

    private function updateItems(int $i): void
    {
        foreach ($this->lists->getItems()->getAll() as $item) {
            if (($i % UPDATE_ITEMS) != ($item->getMagicNumber() % UPDATE_ITEMS)) {
                continue;
            }
            if (!$item->isValidObject()) {
                continue;
            }

            $actionResult = null;

            // Try script first if it exists
            $script = $item->getScript(ScriptType::Update);
            if ($script) {
                $actionResult = Lisp::eval($item, $script);
            }

            // Decay corpses
            if (!$actionResult) {
                if (($item->getTemplate()->hasFlag(ItemFlag::MonsterCorpse) &&
                     $item->getTimeSinceCreation() >= MONSTER_CORPSE_DECAY) ||
                    ($item->getTemplate()->hasFlag(ItemFlag::PlayerCorpse) &&
                     $item->getTimeSinceCreation() >= PLAYER_CORPSE_DECAY)) {
                    $this->extractCorpse($item);
                }
            }
        }
    }

    private function updateRooms(int $i): void
    {
        foreach ($this->lists->getRooms()->getAll() as $room) {
            if (($i % UPDATE_ROOMS) != ($room->getTemplate()->getId() % UPDATE_ROOMS)) {
                continue;
            }

            $actionResult = null;

            // Try script first
            $script = $room->getScript(ScriptType::Update);
            if ($script) {
                $actionResult = Lisp::eval($room, $script);
            }

            // Handle ambient messages
            if (!$actionResult && $room->getTemplate()->getAmbientMessages()) {
                $this->ambientHandler->handleRoom($room);
            }

            // Update exits as well
            foreach ($room->getExits() as $exit) {
                $script = $exit->getScript(ScriptType::Update);
                if ($script) {
                    Lisp::eval($exit, $script);
                }
            }
        }
    }

    private function updateZones(int $i): void
    {
        if ($i % UPDATE_ZONES != 0) {
            return;
        }

        foreach ($this->lists->getZones()->getAll() as $zone) {
            $actionResult = null;

            // Try script first
            $script = $zone->getScript(ScriptType::Update);
            if ($script) {
                $actionResult = Lisp::eval($zone, $script);
            }

            // Reset zone if required
            if (!$actionResult && $zone->requiresReset()) {
                $this->zoneReset->reset($zone, false);
            }
        }
    }

    private function updateTime(int $i): void
    {
        if ($i % UPDATE_TIME != 0) {
            return;
        }

        // Current part of day
        $part = GameTime::now()->getPartOfDay();

        if ($this->lastPartOfDay && $this->lastPartOfDay !== $part) {
            // Part of day has changed, maybe send message to players
            $message = $part->messageToPlayers();

            if ($message) {
                foreach ($this->lists->getLiving()->getAll() as $living) {
                    if (!$living->isPlayer()) {
                        continue;
                    }
                    if ($living->getRoom()->getTemplate()->getTerrain() == Terrain::Underground) {
                        continue;
                    }

                    $living->outln($message);
                }
            }
        }

        $this->lastPartOfDay = $part;
    }

    private function extractCorpse(Item $corpse): void
    {
        $logLevel = 'debug';
        if ($corpse->getTemplate()->hasFlag(ItemFlag::PlayerCorpse)) {
            $logLevel = 'info';
        }

        if ($corpse->getRoom()) {
            Log::add($logLevel, 'The ' . $corpse->getTemplate()->getName() . ' decays in room ' . $corpse->getRoom()->getTemplate()->getId() . '.');

            foreach ($corpse->getRoom()->getLiving()->getAll() as $living) {
                if (!$living->canSeeItem($corpse)) {
                    continue;
                }

                $this->act->toChar("A swarm of maggots consumes @p.", $living, $corpse);
            }

            while (!$corpse->getContents()->empty()) {
                $this->world->itemToRoom($corpse->getContents()->first(), $corpse->getRoom());
            }
        } elseif ($corpse->getContainer()) {
            Log::add($logLevel, 'The ' . $corpse->getTemplate()->getName() . ' decays inside ' . $corpse->getContainer()->getTemplate()->getName() . '.');

            while (!$corpse->getContents()->empty()) {
                $this->world->itemToContainer($corpse->getContents()->first(), $corpse->getContainer());
            }
        } elseif ($corpse->getCarrier()) {
            Log::add($logLevel, 'The ' . $corpse->getTemplate()->getName() . ' decays while carried by ' . $corpse->getCarrier()->getName() . '.');

            $this->act->toChar("@p decays in your hands.", $corpse->getCarrier(), $corpse);

            while (!$corpse->getContents()->empty()) {
                $this->world->itemToInventory($corpse->getContents()->first(), $corpse->getCarrier());
            }
        } elseif ($corpse->getWearer()) {
            // wearing a corpse, pretty wild!

            Log::add($logLevel, 'The ' . $corpse->getTemplate()->getName() . ' decays while worn by ' . $corpse->getWearer()->getName() . '.');

            $slot = $corpse->getWearer()->findCurrentSlot($corpse);
            $text = $slot->renderStringFiller();
            $this->act->toChar("@p @+ decays.", $corpse->getWearer(), $corpse, $text);

            while (!$corpse->getContents()->empty()) {
                $this->world->itemToInventory($corpse->getContents()->first(), $corpse->getWearer());
            }
        } else {
            // should not happen
        }

        $this->world->extractItem($corpse);
    }

    private function moveMonster(Monster $monster): ?Room
    {
        if ($monster->getTemplate()->hasFlag(MonsterFlag::Sentinel) || !Random::permil(25)) {
            return null;
        }

        $availableExits = $monster->getRoom()->getExits($monster);

        if ($availableExits) {
            $dirName = Random::keyFromArray($availableExits);
            return $this->actionMove->move($monster, Direction::from($dirName));
        }

        return null;
    }
}
