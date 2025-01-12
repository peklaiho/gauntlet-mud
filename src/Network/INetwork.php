<?php
/**
 * Gauntlet MUD - Interface for network server
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Network;

interface INetwork
{
    public function initialize(): void;
    public function accept(): ?IConnection;
    public function close(): void;
}
