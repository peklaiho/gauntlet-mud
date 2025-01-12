<?php
/**
 * Gauntlet MUD - Interface for player repository
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Data;

use Gauntlet\Player;

interface IPlayerRepository
{
    public function has(string $name): bool;
    public function findByName(string $name): ?Player;
    public function store(Player $player): bool;
}
