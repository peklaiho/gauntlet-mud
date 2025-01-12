<?php
/**
 * Gauntlet MUD - Interface for modules
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Module;

use Gauntlet\Descriptor;
use Gauntlet\Util\Input;

interface IModule
{
    public function init(Descriptor $desc): void;
    public function processInput(Descriptor $desc, Input $input): void;
    public function prompt(Descriptor $desc): void;
}
