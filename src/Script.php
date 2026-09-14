<?php
/**
 * Gauntlet MUD - Game script
 * Copyright (C) 2017-2026 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use MadLisp\PhpCompiledProgram;

use Gauntlet\Util\Lisp;

class Script
{
    protected PhpCompiledProgram $program;

    public function __construct(
        protected string $code
    ) {
        $this->program = Lisp::compile($code);
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getProgram(): PhpCompiledProgram
    {
        return $this->program;
    }
}
