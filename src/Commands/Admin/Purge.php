<?php
/**
 * Gauntlet MUD - Purge command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Admin;

use Gauntlet\Act;
use Gauntlet\Monster;
use Gauntlet\Player;
use Gauntlet\World;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Input;
use Gauntlet\Util\LivingFinder;
use Gauntlet\Util\Log;

class Purge extends BaseCommand
{
    public function __construct(
        protected Act $act,
        protected World $world
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($input->empty()) {
            Log::admin($player->getName() . ' purged all monsters in room ' . $player->getRoom()->getTemplate()->getId() . '.');

            $this->act->toChar("You make a gesture and cleanse the area of impurities.", $player);
            $this->act->toRoom("@a makes a gesture and cleanses the area of impurities.", true, $player);

            foreach ($player->getRoom()->getLiving()->getAll() as $living) {
                if ($living->isMonster()) {
                    $this->world->extractLiving($living);
                }
            }
        } else {
            $lists = [$player->getRoom()->getLiving()];
            $target = (new LivingFinder($player, $lists))
                ->excludeSelf()
                ->find($input->get(0));

            if (!$target) {
                $player->outln(MESSAGE_NOONE);
                return;
            }

            if ($target->isMonster()) {
                Log::admin($player->getName() . ' purged ' . $target->getName() . ' in room ' . $player->getRoom()->getTemplate()->getId() . '.');
                $this->act->toChar("You make a gesture and @T disintegrates into atoms.", $player, null, $target);
                $this->act->toRoom("@a makes a gesture and @A disintegrates into atoms!", true, $player, null, $target);
                $this->world->extractLiving($target);
            } else {
                $player->outln('Sorry, for now you cannot purge players.');
            }
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return "Cleanse the current room, destroying any NPCs and monsters. You can destroy a single creature by giving it as argument.";
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            '',
            '<monster>'
        ];
    }
}
