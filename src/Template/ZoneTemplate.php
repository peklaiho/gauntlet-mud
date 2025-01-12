<?php
/**
 * Gauntlet MUD - Template for zones
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Template;

use Gauntlet\Enum\ZoneType;

class ZoneTemplate extends BaseTemplate
{
    protected ZoneType $type = ZoneType::Static;
    protected int $interval = 0;
    protected array $range;
    protected array $ops = [];
    protected array $roomTemplates = [];

    public function addRoomTemplate(RoomTemplate $roomTemplate): void
    {
        $this->roomTemplates[] = $roomTemplate;
    }

    public function getType(): ZoneType
    {
        return $this->type;
    }

    public function getInterval(): int
    {
        return $this->interval;
    }

    public function getRange(): array
    {
        return $this->range;
    }

    public function getOps(): array
    {
        return $this->ops;
    }

    public function getRoomTemplates(): array
    {
        return $this->roomTemplates;
    }

    public function ownsRoomId(int $id): bool
    {
        return $id >= $this->range[0] && $id <= $this->range[1];
    }

    public function setType(ZoneType $val): void
    {
        $this->type = $val;
    }

    public function setInterval(int $val): void
    {
        $this->interval = $val;
    }

    public function setRange(array $val): void
    {
        $this->range = $val;
    }

    public function setOps(array $ops): void
    {
        $this->ops = $ops;
    }
}
