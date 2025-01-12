<?php
/**
 * Gauntlet MUD - Zone instance
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Template\ZoneTemplate;
use Gauntlet\Trait\CreationTime;
use Gauntlet\Trait\MagicNumber;

class Zone extends BaseObject
{
    protected Collection $rooms;
    protected int $lastReset;
    protected ?string $owner = null;

    use CreationTime;
    use MagicNumber;

    public function __construct(
        protected ZoneTemplate $template,
        int $magicNum
    ) {
        $this->setCreationTime(time());
        $this->setMagicNumber($magicNum);
        $this->rooms = new Collection();
    }

    public function getRooms(): Collection
    {
        return $this->rooms;
    }

    public function getTemplate(): ZoneTemplate
    {
        return $this->template;
    }

    public function getTimeSinceReset(): int
    {
        return time() - $this->lastReset;
    }

    public function getOwner(): ?string
    {
        return $this->owner;
    }

    public function setResetTime(): void
    {
        $this->lastReset = time();
    }

    public function setOwner(?string $val): void
    {
        $this->owner = $val;
    }

    public function requiresReset(): bool
    {
        return $this->template->getInterval() > 0 && $this->getTimeSinceReset() >= $this->template->getInterval();
    }

    #[\Override]
    public function getTechnicalName(): string
    {
        return "Zone<{$this->id}:{$this->getMagicNumber()}>";
    }
}
