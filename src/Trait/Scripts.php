<?php
/**
 * Gauntlet MUD - Trait for scripts
 * Copyright (C) 2017-2026 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Trait;

use Gauntlet\Script;
use Gauntlet\Enum\ScriptType;

trait Scripts
{
    protected array $scripts = [];

    public function getScript(ScriptType $type): ?Script
    {
        return $this->scripts[$type->value] ?? null;
    }

    public function getScripts(): array
    {
        return $this->scripts;
    }

    public function setScript(ScriptType $type, string $code): void
    {
        $this->scripts[$type->value] = new Script($code);
    }
}
