<?php
/**
 * Gauntlet MUD - Room exit instance
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Enum\Direction;
use Gauntlet\Enum\ExitFlag;
use Gauntlet\Enum\ScriptType;
use Gauntlet\Template\RoomExitTemplate;
use Gauntlet\Trait\MagicNumber;

class RoomExit extends BaseObject
{
    protected bool $closed = false;
    protected bool $locked = false;

    use MagicNumber;

    public function __construct(
        protected RoomExitTemplate $template,
        protected Room $from,
        protected Direction $dir,
        protected Room $to,
        int $magicNum
    ) {
        $this->setMagicNumber($magicNum);
    }

    public function getTemplate(): RoomExitTemplate
    {
        return $this->template;
    }

    public function getFrom(): ?Room
    {
        return $this->from;
    }

    public function getDirection(): Direction
    {
        return $this->dir;
    }

    public function getTo(): Room
    {
        return $this->to;
    }

    public function isDoor(): bool
    {
        return $this->template->getDoorName() !== null;
    }

    public function isClosed(): bool
    {
        return $this->closed;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function isPassable(Living $living): bool
    {
        if ($this->isDoor() && $this->isClosed()) {
            return false;
        }

        if ($living->isMonster()) {
            if ($this->template->hasFlag(ExitFlag::NoMonster)) {
                return false;
            }

            if (in_array($this->template->getRoomId(), $living->getTemplate()->getAvoidRooms())) {
                return false;
            }
        }

        return true;
    }

    public function setClosed(bool $val): void
    {
        $this->closed = $val;
    }

    public function setLocked(bool $val): void
    {
        $this->locked = $val;
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
        return "Exit<{$this->from->getTemplate()->getId()}:{$this->dir->value}:{$this->getMagicNumber()}>";
    }
}
