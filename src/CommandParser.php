<?php
/**
 * Gauntlet MUD - Command parser
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Util\Input;
use Gauntlet\Util\Levenshtein;

class CommandParser
{
    public function __construct(
        protected CommandMap $map
    ) {

    }

    public function parse(Player $player, Input $input): bool
    {
        $cmdName = $input->getCommand();
        $cmdInfo = $this->map->getCommand($cmdName, $player->getAdminLevel());

        if ($cmdInfo) {
            $cmd = SERVICE_CONTAINER->get($cmdInfo->getName());
            $cmd->execute($player, $input, $cmdInfo->getSubcmd());
            return true;
        }

        return false;
    }

    public function suggestion(Player $player, Input $input): ?string
    {
        $list = $this->map->getList($player->getAdminLevel());
        return Levenshtein::findClosest($input->getCommand(), $list, 1);
    }
}
