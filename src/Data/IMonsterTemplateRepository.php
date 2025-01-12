<?php
/**
 * Gauntlet MUD - Interface for monster repository
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Data;

use Gauntlet\Collection;

interface IMonsterTemplateRepository
{
    public function readInto(Collection $list): void;
}
