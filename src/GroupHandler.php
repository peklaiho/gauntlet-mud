<?php
/**
 * Gauntlet MUD - Handler for groups
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Util\Log;

class GroupHandler
{
    private int $nextId = 1;

    public function __construct(
        protected Lists $lists
    ) {

    }

    public function create(Living $living): Group
    {
        $group = new Group($this->nextId++, $living);

        Log::info("{$living->getName()} has created group #{$group->getId()}.");

        $this->join($living, $group);

        $this->lists->getGroups()->add($group);

        return $group;
    }

    public function join(Living $living, Group $group): void
    {
        $living->setGroup($group);
        $group->getMembers()->add($living);

        Log::info("{$living->getName()} has joined group #{$group->getId()}.");
    }

    public function leave(Living $living)
    {
        $group = $living->getGroup();

        $living->setGroup(null);
        $group->getMembers()->remove($living);

        if ($group->getMembers()->empty()) {
            // Remove group from global list
            $this->lists->getGroups()->remove($group);
        } elseif ($group->getLeader() === $living) {
            // Choose next leader
            $group->setLeader($group->getMembers()->first());
        }

        Log::info("{$living->getName()} has left group #{$group->getId()}.");
    }
}
