<?php
/**
 * Gauntlet MUD - Interface for room repository
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Data;

use Gauntlet\Collection;

interface IRoomRepository
{
    public function readInto(Collection $list): void;
}
