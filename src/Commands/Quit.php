<?php
/**
 * Gauntlet MUD - Quit command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands;

use Gauntlet\Act;
use Gauntlet\Player;
use Gauntlet\World;
use Gauntlet\Data\IPlayerRepository;
use Gauntlet\Util\Input;

class Quit extends BaseCommand
{
    public function __construct(
        protected IPlayerRepository $repo,
        protected World $world,
        protected Act $act
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($player->getTarget()) {
            $player->outln("You are fighting for your life!");
            return;
        } elseif (strcasecmp($input->getCommand(), 'quit') != 0) {
            $player->outln("You must type the whole command to quit.");
            return;
        }

        $this->act->toRoom("@t has left the realm.", true, $player);

        $this->repo->store($player);

        $this->world->extractLiving($player);
        $player->getDescriptor()->setPlayer(null);
        $player->getDescriptor()->close();
    }

    public function getDescription(?string $subcmd): string
    {
        return "Leave the game, for now.";
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            '',
        ];
    }
}
