<?php
/**
 * Gauntlet MUD - Interface for network connections
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Network;

interface IConnection
{
    public function getAddress(): string;
    public function getReadBytes(): int;
    public function getWriteBytes(): int;
    public function close(): void;
    public function read(): string;
    public function write(string $data): int;
}
