<?php
/**
 * Gauntlet MUD - Interface for mail repository
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Data;

use Gauntlet\Collection;
use Gauntlet\MailEntry;

interface IMailRepository
{
    public function readInto(string $player, Collection $list): int;
    public function write(string $player, MailEntry $mail): bool;
    public function delete(string $player, string $id): bool;
}
