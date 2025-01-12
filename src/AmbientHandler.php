<?php
/**
 * Gauntlet MUD - Handler for ambient messages
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Util\Random;

class AmbientHandler
{
    public function __construct(
        protected Act $act
    ) {

    }

    public function handleMonster(Monster $monster): bool
    {
        // Return if unlucky
        if (!Random::permil(5)) {
            return false;
        }

        $ambient = Random::fromArray($monster->getTemplate()->getAmbientMessages());

        // Find target if this ambient requires it
        if ($ambient->getVictimMsg()) {
            // Find target candidates
            $candidates = $this->findCandidates($ambient, $monster->getRoom(), $monster);

            if ($candidates) {
                $target = Random::fromArray($candidates);

                $this->act->toRoom($ambient->getRoomMsg(), false, $monster, null, $target, true);
                $this->act->toVict($ambient->getVictimMsg(), false, $monster, null, $target);

                return true;
            }
        } else {
            // Otherwise we just send to room only
            $this->act->toRoom($ambient->getRoomMsg(), false, $monster);
            return true;
        }

        return false;
    }

    public function handleRoom(Room $room): bool
    {
        // Return if unlucky
        if (!Random::permil(5)) {
            return false;
        }

        $ambient = Random::fromArray($room->getTemplate()->getAmbientMessages());

        // Require target always
        $candidates = $this->findCandidates($ambient, $room, null);

        if ($candidates) {
            $target = Random::fromArray($candidates);

            $this->act->toRoom($ambient->getRoomMsg(), false, $target);

            if ($ambient->getVictimMsg()) {
                $this->act->toChar($ambient->getVictimMsg(), $target);
            } else {
                // We need this because toRoom does not show to target itself
                $this->act->toChar($ambient->getRoomMsg(), $target);
            }

            return true;
        }

        return false;
    }

    private function findCandidates(AmbientMessage $ambient, Room $room, ?Monster $monster): array
    {
        $results = [];

        foreach ($room->getLiving()->getAll() as $target) {
            if ($monster) {
                if ($target === $monster) {
                    continue;
                }
                if (!$monster->canSee($target)) {
                    continue;
                }
            }
            if ($target->isMonster()) {
                continue;
            }
            if ($ambient->getVictimSex() && $ambient->getVictimSex() != $target->getSex()) {
                continue;
            }

            $results[] = $target;
        }

        return $results;
    }
}
