<?php
/**
 * Gauntlet MUD - Base class for all instances
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use MadLisp\Env;

use Gauntlet\Enum\ScriptType;
use Gauntlet\Trait\Scripts;
use Gauntlet\Util\Lisp;

abstract class BaseObject
{
    use Scripts;

    private bool $validObject = true;
    private ?Env $lispEnv = null;

    public function isValidObject(): bool
    {
        return $this->validObject;
    }

    public function invalidate(): void
    {
        $this->validObject = false;
    }

    public function createLispEnv(Env $parent): Env
    {
        if (!$this->lispEnv) {
            $this->lispEnv = new Env($this->getTechnicalName(), $parent);
            $this->lispEnv->set('me', $this);
        }

        return $this->lispEnv;
    }

    public function getLispEnv(): ?Env
    {
        return $this->lispEnv;
    }

    public function runInitScript(): bool
    {
        $script = $this->getScript(ScriptType::Init);

        if ($script) {
            Lisp::eval($this, $script);
            return true;
        }

        return false;
    }

    public abstract function getTechnicalName(): string;
}
