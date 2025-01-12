<?php
/**
 * Gauntlet MUD - Trait for scripts
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Trait;

use Gauntlet\Enum\ScriptType;

trait Scripts
{
    protected array $scripts = [];

    public function getScript(ScriptType $type): ?string
    {
        return $this->scripts[$type->value] ?? null;
    }

    public function getScripts(): array
    {
        return $this->scripts;
    }

    public function setScript(ScriptType $type, string $script): void
    {
        $this->scripts[$type->value] = $script;
    }
}
