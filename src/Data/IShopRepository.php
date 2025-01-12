<?php
/**
 * Gauntlet MUD - Interface for shop repository
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Data;

use Gauntlet\Collection;

interface IShopRepository
{
    public function readInto(Collection $list): void;
}
