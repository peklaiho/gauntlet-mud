<?php
/**
 * Gauntlet MUD - Probe command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Admin;

use Gauntlet\BaseObject;
use Gauntlet\Experience;
use Gauntlet\Item;
use Gauntlet\Living;
use Gauntlet\Monster;
use Gauntlet\Player;
use Gauntlet\Room;
use Gauntlet\RoomExit;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Enum\Modifier;
use Gauntlet\Util\Input;
use Gauntlet\Util\ItemFinder;
use Gauntlet\Util\Lisp;
use Gauntlet\Util\LivingFinder;
use Gauntlet\Util\TimeFormatter;

class Probe extends BaseCommand
{
    public function __construct(
        protected ItemFinder $itemFinder
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($input->empty()) {
            $player->outln('Who or what do you wish to probe?');
            return;
        }

        // Find room first
        if (strtolower($input->get(0)) == 'room') {
            $this->showRoom($player, $player->getRoom());
            return;
        }

        // Find monster next
        $lists = [$player->getRoom()->getLiving()];
        $target = (new LivingFinder($player, $lists))
            ->find($input->get(0));

        if ($target) {
            $this->showLiving($player, $target);
            return;
        }

        // Find item last
        $lists = [$player->getRoom()->getItems(), $player->getInventory(), $player->getEquipment()];
        $item = $this->itemFinder->find($player, $input->get(0), $lists);

        if ($item) {
            $this->showItem($player, $item);
            return;
        }

        $player->outln('Nothing and no-one here by that name.');
    }

    public function getDescription(?string $subcmd): string
    {
        return 'Display extensive information about target.';
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            "<'room' | player | item | npc>",
        ];
    }

    private function showRoom(Player $player, Room $room): void
    {
        $player->outln("Stats for room %d: %s", $room->getTemplate()->getId(), $room->getTemplate()->getName());

        $zone = $room->getZone();
        $player->outln("Zone %d: %s (%s, instance %d)", $zone->getTemplate()->getId(),
            $zone->getTemplate()->getName(), $zone->getTemplate()->getType()->value,
            $zone->getMagicNumber());
        if ($zone->getOwner()) {
            $player->outln("Zone owner: %s", $zone->getOwner());
        }

        $player->outln('Terrain: %s', $room->getTemplate()->getTerrain()->value);

        $player->outln("Description: %d characters", strlen(strval($room->getTemplate()->getLongDesc())));

        $player->outln('Flags: %s', $room->getTemplate()->renderFlags());

        $player->outln('Dark: %s', $room->isDark() ? 'true' : 'false');

        $this->showAmbientMessages($player, $room);

        $this->showScripts($player, $room);

        $player->outln('Exits:');
        foreach ($room->getExits() as $dir => $exit) {
            $this->showExit($player, $dir, $exit);
        }
    }

    private function showExit(Player $player, string $dir, RoomExit $exit): void
    {
        $player->outln('* %s: %d', $dir, $exit->getTo()->getTemplate()->getId());
        $this->showScripts($player, $exit);
    }

    private function showItem(Player $player, Item $item): void
    {
        $template = $item->getTemplate();

        $player->outln("Stats for item '%s':", $template->getName());

        if ($item->getRoom()) {
            $player->outln('In room: [%d] %s', $item->getRoom()->getTemplate()->getId(), $item->getRoom()->getTemplate()->getName());
        }
        if ($item->getCarrier()) {
            $player->outln('Carried by: %s', $item->getCarrier()->getName());
        }
        if ($item->getWearer()) {
            $player->outln('Worn by: %s', $item->getWearer()->getName());
        }

        $player->outln(
            "Id: %d, Num: %d, Count: %d, Age: %s",
            $template->getId(),
            $item->getMagicNumber(),
            $template->getCount(),
            TimeFormatter::timeToShortString(time() - $item->getCreationTime())
        );
        $player->outln('Keywords: %s', implode(', ', $template->getKeywords()));
        $player->outln('Flags: %s', $template->renderFlags());

        $this->showScripts($player, $item);

        $player->outln('Weight: %.2f', $item->getWeight());
        $player->outln('Cost: %d', $template->getCost());

        if ($item->isWeapon()) {
            $player->outln('Type: Weapon');

            $player->outln('Required Str: %d', $template->getRequiredStr());

            $player->outln(
                "Damage: %.0f to %.0f (%s, %s)",
                $template->getMinDamage(),
                $template->getMaxDamage(),
                $template->getAttackType()->value,
                $template->getDamageType()->value
            );
        } elseif ($item->isArmor()) {
            $player->outln('Type: Armor');
        } elseif ($item->isContainer()) {
            $player->outln('Type: Container');

            $player->outln('Capacity: %.1f', $template->getCapacity());
            $player->outln('Contains: %d items', $item->getContents()->count());
        } elseif ($item->isBulletinBoard()) {
            $player->outln('Bulletin board messages: %d', $template->getMessages()->count());
        }

        if ($template->getMods()) {
            $player->outln('Mods:');
            foreach ($template->getMods() as $name => $val) {
                $player->outln('  %s: %.1f', $name, $val);
            }
        }
    }

    private function showLiving(Player $player, Living $target): void
    {
        $player->outln("Stats for %s %s '%s':", $target->getSex()->name(), $target->isPlayer() ? 'player' : 'NPC', $target->getName());

        if ($target->isMonster()) {
            $player->outln(
                "Id: %d, Num: %d, Count: %d, Age: %s",
                $target->getTemplate()->getId(),
                $target->getMagicNumber(),
                $target->getTemplate()->getCount(),
                TimeFormatter::timeToShortString(time() - $target->getCreationTime())
            );
            $player->outln('Keywords: %s', implode(', ', $target->getTemplate()->getKeywords()));
            $player->outln('Flags: %s', $target->getTemplate()->renderFlags());
            $player->outln('Faction: %s', $target->getTemplate()->getFaction() ? $target->getTemplate()->getFaction()->getName() : '');

            $avoidRooms = $target->getTemplate()->getAvoidRooms();
            if ($avoidRooms) {
                $player->outln('Avoid rooms: [' . implode(', ', $avoidRooms) . ']');
            }

            $this->showAmbientMessages($player, $target);
        } else {
            $player->outln('Class: %s', $target->getClass()->value);
            $player->outln(
                'Created on: %s, Age: %s',
                gmdate('Y-m-d H:i:s', $target->getCreationTime()),
                TimeFormatter::timeToShortString(time() - $target->getCreationTime())
            );
            if ($target->getAdminLevel()) {
                $player->outln('Admin level: %s', $target->getAdminLevel()->name());
            }
        }

        $this->showScripts($player, $target);

        $player->outln('Size: %s (%.0f kg)', $target->getSize()->value, $target->getWeight());

        $player->outln('Level: %d', $target->getLevel());
        $player->outln('Experience: %d', $target->getExperience());
        if ($target->isMonster()) {
            $player->outln('Exp Multip: %.1f', Experience::getPenaltyMultiplier($player->getLevel(), $target->getLevel()));
        }
        $player->outln('Coins: %d', $target->getCoins());
        $player->outln('Attributes: STR %d, DEX %d, INT %d, CON %d', $target->getStr(), $target->getDex(), $target->getInt(), $target->getCon());

        $player->outln("Health: %.0f / %.0f", $target->getHealth(), $target->getMaxHealth());
        if ($target->isPlayer()) {
            $player->outln("Mana: %.0f / %.0f", $target->getMana(), $target->getMaxMana());
            $player->outln("Move: %.0f / %.0f", $target->getMove(), $target->getMaxMove());
        }

        $player->outln('Attack: %s (%d hits)', $target->getAttackType()->value, $target->getNumAttacks());
        $avgDam = $target->getMinDamage() + (($target->getMaxDamage() - $target->getMinDamage()) / 2) + $target->getBonusDamage();
        $player->outln(
            "Damage: %.1f to %.1f %s %.1f (avg %.1f) (%s)",
            $target->getMinDamage(),
            $target->getMaxDamage(),
            $target->getBonusDamage() >= 0 ? '+' : '-',
            abs($target->getBonusDamage()),
            $avgDam,
            $target->getDamageType()->value
        );

        $player->outln("Chance to hit bonus: %d %%" , $target->bonusToHit());
        $player->outln("Chance to dodge bonus: %d %%", $target->bonusToDodge());
        $player->outln("Damage reduction: %.1f", $target->getMod(Modifier::Armor));
    }

    private function showAmbientMessages(Player $player, Room|Monster $target): void
    {
        $ambient = $target->getTemplate()->getAmbientMessages();

        if ($ambient) {
            $player->outln("Ambient messages:");
            foreach ($ambient as $amb) {
                $player->outln('  ' . $amb->getRoomMsg());
            }
        }
    }

    private function showScripts(Player $player, BaseObject $object): void
    {
        $env = $object->getLispEnv();
        if ($env) {
            $player->outln("Environment:");
            foreach ($env->getData() as $key => $val) {
                if ($key == 'me') {
                    continue;
                }

                $player->outln('  %s: %s', $key, Lisp::toString($val, true));
            }
        }

        $scripts = $object->getScripts();
        if ($scripts) {
            $player->outln("Scripts:");
            foreach ($scripts as $key => $val) {
                $player->outln('  %6s: %s', $key, $val);
            }
        }
    }

    private function actionToString(array $action): string
    {
        $name = $action[0];
        $args = $action[1];

        if ($args) {
            return "$name " . $this->arrayToString($args);
        }

        return $name;
    }

    private function arrayToString(array $arr): string
    {
        $content = [];
        foreach ($arr as $key => $val) {
            $content[] = (is_numeric($key) ? '' : "$key:") . (is_array($val) ? $this->arrayToString($val) : $val);
        }

        return '[' . implode(',', $content) . ']';
    }
}
