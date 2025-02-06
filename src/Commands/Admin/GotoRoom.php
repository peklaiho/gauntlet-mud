<?php
/**
 * Gauntlet MUD - Goto command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Admin;

use Gauntlet\Act;
use Gauntlet\Lists;
use Gauntlet\Player;
use Gauntlet\Renderer;
use Gauntlet\World;
use Gauntlet\Enum\ZoneType;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Input;
use Gauntlet\Util\LivingFinder;

class GotoRoom extends BaseCommand
{
    public function __construct(
        protected Renderer $renderer,
        protected World $world,
        protected Lists $lists,
        protected Act $act
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($input->empty()) {
            $player->outln("Where do you want to go?");
            return;
        }

        if (filter_var($input->get(0), FILTER_VALIDATE_INT)) {
            $room = null;

            if ($input->count() >= 2) {
                // Two arguments, second is zone instance
                foreach ($this->lists->getZones()->getAll() as $zone) {
                    if ($zone->getMagicNumber() == $input->get(1)) {
                        $room = $zone->getRooms()->get($input->get(0));
                        break;
                    }
                }
            } else {
                // Try current zone first
                $room = $player->getRoom()->getZone()->getRooms()->get($input->get(0));

                // Then try static zones
                if (!$room) {
                    foreach ($this->lists->getZones()->getAll() as $zone) {
                        if ($zone->getTemplate()->getType() == ZoneType::Static) {
                            $room = $zone->getRooms()->get($input->get(0));
                            if ($room) {
                                break;
                            }
                        }
                    }
                }
            }

            if (!$room) {
                $player->outln("Unable to find room by that id.");
                return;
            }
        } else {
            $lists = [$this->lists->getLiving()];
            $target = (new LivingFinder($player, $lists))
                ->excludeSelf()
                ->find($input->get(0));

            if ($target) {
                $room = $target->getRoom();
            } else {
                $player->outln("Unable to find any target by that name.");
                return;
            }
        }

        $this->act->toRoom("@t fades out of existence.", true, $player);
        $this->world->livingToRoom($player, $room);
        $this->act->toRoom("@a fades into existence.", true, $player);
        $this->renderer->renderRoom($player, $room);
    }

    public function getDescription(?string $subcmd): string
    {
        return "Teleport to room, monster or player.";
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            '<room | monster | player>',
        ];
    }
}
