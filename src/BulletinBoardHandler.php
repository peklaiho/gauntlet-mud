<?php
/**
 * Gauntlet MUD - Handler for bulletin boards
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Data\IBulletinBoardRepository;
use Gauntlet\Util\Log;

class BulletinBoardHandler
{
    public function __construct(
        protected IBulletinBoardRepository $repo
    ) {

    }
}
