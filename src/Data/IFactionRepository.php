<?php
/**
 * Gauntlet MUD - Interface for faction repository
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Data;

use Gauntlet\Collection;

interface IFactionRepository
{
    public function readInto(Collection $list): void;
}
