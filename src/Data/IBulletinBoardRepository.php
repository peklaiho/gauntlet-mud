<?php
/**
 * Gauntlet MUD - Interface for bulletin board repository
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Data;

use Gauntlet\Collection;
use Gauntlet\BulletinBoardEntry;

interface IBulletinBoardRepository
{
    public function readInto(string $boardId, Collection $list): int;
    public function write(string $boardId, BulletinBoardEntry $entry): bool;
    public function delete(string $boardId, string $entryId): bool;
}
