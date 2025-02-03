<?php
/**
 * Gauntlet MUD - Command information
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Commands\BaseCommand;
use Gauntlet\Enum\AdminLevel;

class CommandInfo
{
    public function __construct(
        protected string $alias,
        protected BaseCommand $cmd,
        protected ?string $subcmd = null,
        protected ?AdminLevel $admin = null
    ) {

    }

    public function getAdmin(): ?AdminLevel
    {
        return $this->admin;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getCommand(): BaseCommand
    {
        return $this->cmd;
    }

    public function getSubcmd(): ?string
    {
        return $this->subcmd;
    }
}
