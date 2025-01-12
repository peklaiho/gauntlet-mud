<?php
/**
 * Gauntlet MUD - Equipment command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Info;

use Gauntlet\Player;
use Gauntlet\Renderer;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Input;

class Equipment extends BaseCommand
{
    public function __construct(
        protected Renderer $render
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        $player->outln("You are wearing:");
        $equipment = $this->render->renderEquipment($player, $player->getEquipment(), $input->count() > 0, true);

        if ($equipment) {
            foreach ($equipment as $eq) {
                $player->outln($eq);
            }
        } else {
            $player->outln('Nothing!');
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return 'List worn equipment and weapons. Using optional parameter will list all equipment slots, even if they are empty.';
    }

    public function getUsage(?string $subcmd): array
    {
        return ["['all']"];
    }

    public function getSeeAlso(?string $subcmd): array
    {
        return ['items', 'remove', 'wear', 'wield'];
    }
}
