<?php
/**
 * Gauntlet MUD - Group of players
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

class Group extends BaseObject
{
    protected Collection $members;
    protected Collection $invitees;

    public function __construct(
        protected int $id,
        protected Living $leader
    ) {
        $this->members = new Collection();
        $this->invitees = new Collection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLeader(): Living
    {
        return $this->leader;
    }

    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function getInvitees(): Collection
    {
        return $this->invitees;
    }

    public function setLeader(Living $living): void
    {
        $this->leader = $living;
    }

    #[\Override]
    public function getTechnicalName(): string
    {
        return "Group<{$this->getId()}>";
    }
}
