<?php
/**
 * Gauntlet MUD - Look command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Info;

use Gauntlet\Act;
use Gauntlet\Item;
use Gauntlet\Living;
use Gauntlet\Player;
use Gauntlet\Renderer;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Input;
use Gauntlet\Util\ItemFinder;
use Gauntlet\Util\LivingFinder;
use Gauntlet\Util\Preferences;

class Look extends BaseCommand
{
    public function __construct(
        protected Renderer $render,
        protected ItemFinder $itemFinder,
        protected Act $act
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if (!$player->canSeeRoom()) {
            $player->outln(MESSAGE_DARK);
            return;
        }

        if ($input->count() >= 2 && strcasecmp($input->get(0), 'in') == 0) {
            $this->lookInContainer($player, $input->get(1));
        } elseif ($input->count() >= 1) {
            $keyword = $input->get(0);
            if ($input->count() >= 2 && strcasecmp($keyword, 'at') == 0) {
                $keyword = $input->get(1);
            }

            $this->lookAtKeyword($player, $keyword);
        } else {
            $this->render->renderRoom($player, $player->getRoom());
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return "Display information about the current room. Also display information " .
            "about the targeted player, item, NPC or keyword. Finally, the 'look' command can " .
            "be used to list the contents of a container.";
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            '',
            "['at'] <player | item | npc | keyword>",
            "<'in'> <container>",
        ];
    }

    private function lookAtItem(Player $player, Item $item): void
    {
        if ($item->getTemplate()->getLongDesc()) {
            $player->outpr($player->highlight($item->getTemplate()->getLongDesc()));
        } else {
            $player->outln("You see nothing special about it.");
        }
    }

    private function lookAtKeyword(Player $player, string $keyword): void
    {
        // Try to find extra description
        $extra = $player->getRoom()->getTemplate()->getExtraDesc($keyword);
        if ($extra) {
            $player->outpr($player->highlight($extra));
            return;
        }

        // Try to find living
        $lists = [$player->getRoom()->getLiving()];
        $living = (new LivingFinder($player, $lists))
            ->find($keyword);
        if ($living) {
            $this->lookAtLiving($player, $living);
            return;
        }

        // Try to find item
        $lists = [$player->getRoom()->getItems(), $player->getInventory(), $player->getEquipment()];
        $item = $this->itemFinder->find($player, $keyword, $lists);
        if ($item) {
            $this->lookAtItem($player, $item);
            return;
        }

        $player->outln(MESSAGE_NOTHING);
    }

    private function lookAtLiving(Player $player, Living $living): void
    {
        if ($living->isPlayer()) {
            $desc = trim($living->getPreference(Preferences::DESCRIPTION, ''));
            if ($desc) {
                $player->outpr(str_replace("\n", " ", $desc));
            } else {
                $this->act->toChar("You see nothing special about @M.", $player, null, $living);
            }
        } else {
            if ($living->getTemplate()->getLongDesc()) {
                $player->outpr($living->getTemplate()->getLongDesc());
            } else {
                $this->act->toChar("You see nothing special about @M.", $player, null, $living);
            }
        }

        // Show condition
        $condition = $this->render->renderCondition($player, $living);
        $this->act->toChar("@E " . $condition, $player, null, $living);

        // Show equipment
        $equipment = $this->render->renderEquipment($player, $living->getEquipment(), false, false);
        if ($equipment) {
            $player->outln();
            foreach ($equipment as $eq) {
                $player->outln($eq);
            }
        }

        // Show inventory for admin
        if ($player->getAdminLevel()) {
            $player->outln();
            $this->act->toChar('@E is carrying:', $player, null, $living);
            $output = $this->render->renderItems($player, $living->getInventory());
            if (!$output) {
                $player->outln('Nothing!');
            }
        }

        // Inform target
        if ($player !== $living) {
            $this->act->toVict("@a looks at you.", true, $player, null, $living);
        }
    }

    private function lookInContainer(Player $player, string $name): void
    {
        $lists = [$player->getInventory(), $player->getEquipment(), $player->getRoom()->getItems()];
        $isContainer = fn ($a) => $a->isContainer();
        $container = $this->itemFinder->find($player, $name, $lists, $isContainer);

        if (!$container) {
            $player->outln('There is no container here by that name.');
            return;
        }

        $this->act->toChar('@p (@+) contains:', $player, $container, $container->getLocation());

        if (!$this->render->renderItems($player, $container->getContents())) {
            $player->outln('Nothing!');
        }
    }

    public function getSeeAlso(?string $subcmd): array
    {
        return ['exits', 'sense'];
    }
}
