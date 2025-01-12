<?php
/**
 * Gauntlet MUD - Load command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Admin;

use Gauntlet\Act;
use Gauntlet\Lists;
use Gauntlet\Player;
use Gauntlet\World;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Input;
use Gauntlet\Util\Log;

class Load extends BaseCommand
{
    public function __construct(
        protected World $world,
        protected Lists $lists,
        protected Act $act
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($input->count() < 2) {
            $player->outln('What do you wish to load?');
            return;
        }

        $qty = $input->get(2, 1);
        if (!is_numeric($qty) || $qty < 1 || $qty > 100) {
            $player->outln('Invalid quantity.');
            return;
        }

        if (str_starts_with_case('item', $input->get(0))) {
            $template = $this->lists->getItemTemplates()->get($input->get(1));
            if ($template) {
                for ($i = 0; $i < $qty; $i++) {
                    $item = $this->world->loadItemToInventory($template, $player);
                }

                $logMessage = $player->getName() . ' loaded item ' . $input->get(1) . ' (' . $template->getName() . ')';
                if ($qty > 1) {
                    $this->act->toChar("You create @o (x$qty).", $player, $item);
                    $logMessage .= " (x$qty)";
                } else {
                    $this->act->toChar("You create @o.", $player, $item);
                }
                Log::admin($logMessage . '.');
                $this->act->toRoom("@a gestures and @s pockets glow with a white light.", true, $player);
            } else {
                $player->outln('No item found by that id.');
            }
        } elseif (str_starts_with_case('monster', $input->get(0))) {
            $template = $this->lists->getMonsterTemplates()->get($input->get(1));
            if ($template) {
                for ($i = 0; $i < $qty; $i++) {
                    $monster = $this->world->loadMonster($template, $player->getRoom());
                }

                $logMessage = $player->getName() . ' loaded monster ' . $input->get(1) . ' (' . $template->getName() .
                    ') in room ' . $player->getRoom()->getTemplate()->getId();
                if ($qty > 1) {
                    $this->act->toChar("You create $qty @X.", $player, null, $monster);
                    $this->act->toRoom("@a gestures and $qty @X appear!", true, $player, null, $monster);
                    $logMessage .= " (x$qty)";
                } else {
                    $this->act->toChar("You create @A.", $player, null, $monster);
                    $this->act->toRoom("@a gestures and @A appears!", true, $player, null, $monster);
                }
                Log::admin($logMessage . '.');
            } else {
                $player->outln('No monster found by that id.');
            }
        } else {
            $player->outln('What do you wish to load?');
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return 'Load an item or monster into the game. Loaded items will be placed in your inventory. You can give optional argument to specify quantity (default is 1).';
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            "'item' <id> [qty]",
            "'monster' <id> [qty]",
        ];
    }
}
