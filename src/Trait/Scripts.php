<?php
/**
 * Gauntlet MUD - Trait for scripts
 * Copyright (C) 2017-2026 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Trait;

use MadLisp\PhpCompiledProgram;

use Gauntlet\Enum\ScriptType;
use Gauntlet\Util\Lisp;

trait Scripts
{
    protected array $scripts = [];

    public function getScript(ScriptType $type): ?PhpCompiledProgram
    {
        return $this->scripts[$type->value] ?? null;
    }

    public function getScripts(): array
    {
        return $this->scripts;
    }

    public function setScript(ScriptType $type, string $code): void
    {
        $script = Lisp::compile($code);

        $this->scripts[$type->value] = $script;
    }
}
