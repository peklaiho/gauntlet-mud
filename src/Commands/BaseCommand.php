<?php
/**
 * Gauntlet MUD - Base class for commands
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands;

use Gauntlet\Player;
use Gauntlet\Util\Input;

abstract class BaseCommand
{
    public abstract function execute(Player $player, Input $input, ?string $subcmd): void;
    public abstract function getDescription(?string $subcmd): string;
    public abstract function getUsage(?string $subcmd): array;

    public function getContextHelp(?string $subcmd): ?array
    {
        return null;
    }

    public function getSeeAlso(?string $subcmd): array
    {
        return [];
    }
}
