<?php
/**
 * Gauntlet MUD - Movement actions
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Enum\Direction;
use Gauntlet\Enum\ScriptType;
use Gauntlet\Util\Lisp;

/**
 * Actions related to movement.
 */
class ActionMove
{
    public function __construct(
        protected World $world,
        protected Act $act,
        protected Renderer $render
    ) {

    }

    public function move(Living $living, Direction $dir): ?Room
    {
        return $this->performMove($living, $dir, false);
    }

    public function flee(Living $living, Direction $dir): ?Room
    {
        return $this->performMove($living, $dir, true);
    }

    private function performMove(Living $living, Direction $dir, bool $flee): ?Room
    {
        $oldRoom = $living->getRoom();
        $exit = $oldRoom->getExit($dir);
        $newRoom = $exit->getTo();

        // Run Lisp script
        $script = $exit->getScript(ScriptType::Entry);
        if ($script) {
            $data = [
                'living' => $living
            ];
            $lispResult = Lisp::evalWithData($exit, $script, $data);
            if ($lispResult) {
                // Truthy value means entry is denied!
                return null;
            }
        }

        $verb = $flee ? 'flee' : 'leave';

        // Check movement points
        if ($living->isPlayer() && !$this->spendMovement($living, $newRoom)) {
            $living->outln("You are too exhausted to $verb.");
            return null;
        }

        $this->act->toRoom("@a {$verb}s @+.", true, $living, null, $dir->name());
        $this->world->livingToRoom($living, $newRoom);
        $this->act->toRoom('@a arrives from @+.', true, $living, null, $dir->oppositeName());

        if (!$flee) {
            $this->handleFollowers($living, $dir, $oldRoom, $exit, $newRoom);
        }

        return $newRoom;
    }

    private function handleFollowers(Living $living, Direction $dir, Room $oldRoom, RoomExit $exit, Room $newRoom): void
    {
        foreach ($oldRoom->getLiving()->getAll() as $follower) {
            if ($follower->shouldFollow($living) && $follower->canSee($living) && $exit->isPassable($follower)) {
                $this->performFollow($living, $follower, $dir, $oldRoom, $exit, $newRoom);
            }
        }
    }

    private function performFollow(Living $living, Living $follower, Direction $dir, Room $oldRoom, RoomExit $exit, Room $newRoom): void
    {
        if ($follower->isPlayer() && !$this->spendMovement($follower, $newRoom)) {
            $follower->outln('You are too exhausted to follow.');
            return;
        }

        $this->act->toRoom("@a follows @M.", true, $follower, null, $living, true);
        $this->world->livingToRoom($follower, $newRoom);
        $this->act->toRoom('@a arrives from ' . $dir->oppositeName() . '.', true, $follower, null, $living, true);

        $this->act->toChar('You follow @M.', $follower, null, $living);
        $this->act->toVict('@a follows you.', true, $follower, null, $living);

        $this->handleFollowers($follower, $dir, $oldRoom, $exit, $newRoom);

        if ($follower->isPlayer()) {
            $this->render->renderRoom($follower, $newRoom, true);
        }
    }

    private function spendMovement(Player $player, Room $room): bool
    {
        $moveCost = $room->getTemplate()->getTerrain()->moveCost();

        // Double cost if encumbered
        if ($player->isEncumbered()) {
            $moveCost *= 2;
        }

        // Allow movement to go negative for admins
        if ($moveCost > $player->getMove() && !$player->getAdminLevel()) {
            return false;
        }

        $player->setMove($player->getMove() - $moveCost);
        return true;
    }
}
