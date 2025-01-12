<?php
/**
 * Gauntlet MUD - Lisp functions
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use MadLisp\CoreFunc;
use MadLisp\Env;
use MadLisp\Hash;
use MadLisp\Seq;
use MadLisp\Vector;
use MadLisp\Lib\ILib;

use Gauntlet\ZoneReset;
use Gauntlet\Enum\Attack;
use Gauntlet\Enum\Damage;
use Gauntlet\Enum\Direction;
use Gauntlet\Enum\EqSlot;
use Gauntlet\Enum\ExitFlag;
use Gauntlet\Enum\ItemFlag;
use Gauntlet\Enum\Modifier;
use Gauntlet\Enum\MonsterFlag;
use Gauntlet\Enum\RoomFlag;
use Gauntlet\Enum\Sex;
use Gauntlet\Util\Log;
use Gauntlet\Util\Preferences;
use Gauntlet\Util\Random;

class LispFuncs implements ILib
{
    public function __construct(
        protected Act $act,
        protected Action $action,
        protected ActionMove $actionMove,
        protected Fight $fight,
        protected ZoneReset $zoneReset,
        protected Socials $socials,
        protected World $world,
        protected Lists $lists
    ) {

    }

    public function register(Env $env): void
    {
        $this->registerPredicates($env);
        $this->registerGetters($env);
        $this->registerSetters($env);
        $this->registerActions($env);
        $this->registerWorldFunctions($env);
        $this->registerMiscFunctions($env);
        $this->registerHelpers($env);
    }

    private function registerPredicates(Env $env): void
    {
        $env->set('is-armor?', new CoreFunc('is-armor?', 'Return true if argument is a piece of armor.', 1, 1,
            fn (Item $a) => $a->isArmor()
        ));

        $env->set('can-see?', new CoreFunc('can-see?', 'Return true if the given living entity (first argument) can see the given item or living entity (second argument).', 1, 2,
            function (Living $a, Living|Item|null $b = null) {
                if (func_num_args() == 1 || $b === null) {
                    return $a->canSeeRoom();
                } elseif ($b instanceof Item) {
                    return $a->canSeeItem($b);
                } else {
                    return $a->canSee($b);
                }
            }
        ));

        $env->set('exit-closed?', new CoreFunc('exit-closed?', 'Return true if the given exit is closed.', 1, 1,
            fn (RoomExit $a) => $a->isClosed()
        ));

        $env->set('is-container?', new CoreFunc('is-container?', 'Return true if argument is a container.', 1, 1,
            fn (Item $a) => $a->isContainer()
        ));

        $env->set('has-door?', new CoreFunc('has-door?', 'Return true if the given exit has a door.', 1, 1,
            fn (RoomExit $a) => $a->isDoor()
        ));

        $env->set('has-flag?', new CoreFunc('has-flag?', 'Return true if the given room, exit, item or monster has the given flag.', 2, 2,
            function (Room|RoomExit|Monster|Item $a, string $flagName) {
                $obj = $a->getTemplate();

                if ($a instanceof Room) {
                    $flag = RoomFlag::tryFrom($flagName);
                } elseif ($a instanceof RoomExit) {
                    $flag = ExitFlag::tryFrom($flagName);
                } elseif ($a instanceof Monster) {
                    $flag = MonsterFlag::tryFrom($flagName);
                } else {
                    $flag = ItemFlag::tryFrom($flagName);
                }

                if (!$flag) {
                    return null;
                }

                return $obj->hasFlag($flag);
            }
        ));

        $env->set('has-light?', new CoreFunc('has-light?', 'Return true if the given player has a light source.', 1, 1,
            fn (Player $a) => $a->hasLight()
        ));

        $env->set('is-exit?', new CoreFunc('is-exit?', 'Return true if argument is an exit.', 1, 1,
            fn ($a) => $a instanceof RoomExit
        ));

        $env->set('is-faction?', new CoreFunc('is-faction?', 'Return true if argument is a faction.', 1, 1,
            fn ($a) => $a instanceof Faction
        ));

        $env->set('is-female?', new CoreFunc('is-female?', 'Return true if the given living entity is a female.', 1, 1,
            fn (Living $a) => $a->getSex() == Sex::Female
        ));

        $env->set('is-group?', new CoreFunc('is-group?', 'Return true if argument is a group.', 1, 1,
            fn ($a) => $a instanceof Group
        ));

        $env->set('is-item?', new CoreFunc('is-item?', 'Return true if argument is an item.', 1, 1,
            fn ($a) => $a instanceof Item
        ));

        $env->set('is-living?', new CoreFunc('is-living?', 'Return true if argument is a living entity.', 1, 1,
            fn ($a) => $a instanceof Living
        ));

        $env->set('exit-locked?', new CoreFunc('exit-locked?', 'Return true if the given exit is locked.', 1, 1,
            fn (RoomExit $a) => $a->isLocked()
        ));

        $env->set('is-male?', new CoreFunc('is-male?', 'Return true if the given living entity is a male.', 1, 1,
            fn (Living $a) => $a->getSex() == Sex::Male
        ));

        $env->set('is-monster?', new CoreFunc('is-monster?', 'Return true if argument is a monster.', 1, 1,
            fn ($a) => $a instanceof Monster
        ));

        $env->set('is-player?', new CoreFunc('is-player?', 'Return true if argument is a player.', 1, 1,
            fn ($a) => $a instanceof Player
        ));

        $env->set('is-room?', new CoreFunc('is-room?', 'Return true if argument is a room.', 1, 1,
            fn ($a) => $a instanceof Room
        ));

        $env->set('is-dark?', new CoreFunc('is-dark?', 'Return true if the given room is dark.', 1, 1,
            fn (Room $a) => $a->isDark()
        ));

        $env->set('is-shop?', new CoreFunc('is-shop?', 'Return true if argument is a shop.', 1, 1,
            fn ($a) => $a instanceof Shop
        ));

        $env->set('is-weapon?', new CoreFunc('is-weapon?', 'Return true if argument is a weapon.', 1, 1,
            fn (Item $a) => $a->isWeapon()
        ));

        $env->set('is-zone?', new CoreFunc('is-zone?', 'Return true if argument is a zone.', 1, 1,
            fn ($a) => $a instanceof Zone
        ));
    }

    private function registerGetters(Env $env)
    {
        $env->set('get-admin', new CoreFunc('get-admin', 'Return the admin level of the given player.', 1, 1,
            fn (Player $a) => $a->getAdminLevel() ? $a->getAdminLevel()->value : 0
        ));

        $env->set('get-age', new CoreFunc('get-age', 'Return the age of the item, zone or living entity in seconds.', 1, 1,
            function (Item|Living|Zone $a) {
                if ($a instanceof Zone) {
                    return $a->getTimeSinceReset();
                } else {
                    return $a->getTimeSinceCreation();
                }
            }
        ));

        $env->set('get-str', new CoreFunc('get-str', 'Return the strength of the given living entity.', 1, 1,
            fn (Living $a) => $a->getStr()
        ));

        $env->set('get-dex', new CoreFunc('get-dex', 'Return the dexterity of the given living entity.', 1, 1,
            fn (Living $a) => $a->getDex()
        ));

        $env->set('get-int', new CoreFunc('get-int', 'Return the intelligence of the given living entity.', 1, 1,
            fn (Living $a) => $a->getInt()
        ));

        $env->set('get-con', new CoreFunc('get-con', 'Return the constitution of the given living entity.', 1, 1,
            fn (Living $a) => $a->getCon()
        ));

        $env->set('get-att-type', new CoreFunc('get-att-type', 'Return the attack type of the given weapon or living entity.', 1, 1,
            function (Item|Living $a) {
                $result = Attack::Hit;
                if ($a instanceof Living) {
                    $result = $a->getAttackType();
                } elseif ($a->isWeapon()) {
                    $result = $a->getTemplate()->getAttackType();
                }
                return $result->value;
            }
        ));

        $env->set('get-carrier', new CoreFunc('get-carrier', 'Return the living entity that is carrying this item or null.', 1, 1,
            fn (Item $a) => $a->getCarrier()
        ));

        $env->set('get-container', new CoreFunc('get-container', 'Return the container that contains the given item or null.', 1, 1,
            fn (Item $a) => $a->getContainer()
        ));

        $env->set('get-capacity', new CoreFunc('get-capacity', 'Return the capacity of the given container.', 1, 1,
            fn (Item $a) => $a->isContainer() ? $a->getTemplate()->getCapacity() : 0
        ));

        $env->set('get-coins', new CoreFunc('get-coins', 'Return the number of coins carried by the given living entity.', 1, 1,
            fn (Living $a) => $a->getCoins()
        ));

        $env->set('get-bank', new CoreFunc('get-bank', 'Return the number of coins the given player has in the bank.', 1, 1,
            fn (Player $a) => $a->getBank()
        ));

        $env->set('get-cost', new CoreFunc('get-cost', 'Return the cost of the given item.', 1, 1,
            fn (Item $a) => $a->getTemplate()->getCost()
        ));

        $env->set('get-current-slot', new CoreFunc('get-current-slot', 'Find equipment slot where the given item is currently equipped.', 1, 1,
            function (Item $item) {
                $wearer = $item->getWearer();
                if ($wearer) {
                    $slot = $wearer->findCurrentSlot($item);
                    return $slot ? $slot->value : null;
                }
                return null;
            }
        ));

        $env->set('get-max-dam', new CoreFunc('get-max-dam', 'Return the maximum damage of the given weapon or living entity.', 1, 1,
            function (Item|Living $a) {
                $result = 1;
                if ($a instanceof Living) {
                    $result = $a->getMaxDamage();
                } elseif ($a->isWeapon()) {
                    $result = $a->getTemplate()->getMaxDamage();
                }
                return $result;
            }
        ));

        $env->set('get-min-dam', new CoreFunc('get-min-dam', 'Return the minimum damage of the given weapon or living entity.', 1, 1,
            function (Item|Living $a) {
                $result = 1;
                if ($a instanceof Living) {
                    $result = $a->getMinDamage();
                } elseif ($a->isWeapon()) {
                    $result = $a->getTemplate()->getMinDamage();
                }
                return $result;
            }
        ));

        $env->set('get-dam-type', new CoreFunc('get-dam-type', 'Return the damage type of the given weapon or living entity.', 1, 1,
            function (Item|Living $a) {
                $result = Damage::Physical;
                if ($a instanceof Living) {
                    $result = $a->getDamageType();
                } elseif ($a->isWeapon()) {
                    $result = $a->getTemplate()->getDamageType();
                }
                return $result->value;
            }
        ));

        $env->set('get-desc', new CoreFunc('get-desc', "Return the description of the given object.", 1, 1,
            function (BaseObject $a) {
                if ($a instanceof Item || $a instanceof Monster) {
                    return $a->getTemplate()->getLongDesc();
                } elseif ($a instanceof Room) {
                    return $a->getDescription();
                } elseif ($a instanceof Player) {
                    return $a->getPreference(Preferences::DESCRIPTION);
                }

                return null;
            }
        ));

        $env->set('get-short-desc', new CoreFunc('get-short-desc', "Return the short description of the given monster or item.", 1, 1,
            fn (Item|Monster $a) => $a->getTemplate()->getShortDesc()
        ));

        $env->set('find-empty-slot', new CoreFunc('find-empty-slot', 'Find empty equipment slot for the item (second argument) for the given living entity (first argument).', 2, 2,
            function (Living $a, Item $item) {
                $slot = $a->findEmptySlot($item);
                return $slot ? $slot->value : null;
            }
        ));

        $env->set('get-equipment', new CoreFunc('get-equipment', 'Return the equipment of the given living entity as a hash-map.', 1, 1,
            fn (Living $a) => new Hash($a->getEquipment()->getAll())
        ));

        $env->set('get-exit-to', new CoreFunc('get-exit-to', 'Return the room where the given exit leads to.', 1, 1,
            fn (RoomExit $a) => $a->getTo()
        ));

        $env->set('get-exit-key', new CoreFunc('get-exit-key', 'Return the key id of the given exit (or null if no key).', 1, 1,
            fn (RoomExit $a) => $a->getTemplate()->getKeyId()
        ));

        $env->set('get-exp', new CoreFunc('get-exp', 'Return the experience of the given living entity.', 1, 1,
            fn (Living $a) => $a->getExperience()
        ));

        $env->set('get-faction', new CoreFunc('get-faction', 'Return the faction of the given monster.', 1, 1,
            fn (Monster $a) => $a->getTemplate()->getFaction()
        ));

        $env->set('get-fondness', new CoreFunc('get-fondness', 'Return the fondness of the given monster (first argument) towards the other living entity (second argument).', 2, 2,
            fn (Monster $a, Living $other) => $a->getFondness($other)->value
        ));

        $env->set('get-group', new CoreFunc('get-group', 'Return the group of the living entity.', 1, 1,
            fn (Living $a) => $a->getGroup()
        ));

        $env->set('get-group-leader', new CoreFunc('get-group-leader', 'Return the leader of the given group.', 1, 1,
            fn (Group $a) => $a->getLeader()
        ));

        $env->set('get-health', new CoreFunc('get-health', 'Return the health of the given living entity.', 1, 1,
            fn (Living $a) => $a->getHealth()
        ));

        $env->set('get-max-health', new CoreFunc('get-max-health', 'Return the maximum health of the given living entity.', 1, 1,
            fn (Living $a) => $a->getMaxHealth()
        ));

        $env->set('get-id', new CoreFunc('get-id', 'Return the id of the given object.', 1, 1,
            function (BaseObject $a) {
                if ($a instanceof Item || $a instanceof Monster) {
                    return $a->getTemplate()->getId();
                } elseif ($a instanceof Player) {
                    return $a->getName(); // name for players
                } elseif (method_exists($a, 'getId')) {
                    return $a->getId();
                }

                return null;
            }
        ));

        $env->set('get-eq-in-slot', new CoreFunc('get-eq-in-slot', 'Return the equipment that the living entity (first argument) is wearing in the given equipment slot (second argument).', 2, 2,
            fn (Living $a, string $slot) => $a->getEqInSlot(EqSlot::from($slot))
        ));

        $env->set('get-slots', new CoreFunc('get-slots', 'Return the equipment slots of the given item.', 1, 1,
            function (Item $a) {
                $result = [];
                foreach ($a->getTemplate()->getSlots() as $slot) {
                    $result[] = $slot->value;
                }
                return new Vector($result);
            }
        ));

        $env->set('get-items', new CoreFunc('get-items', 'Return the items of the given room, container or living entity as a vector.', 1, 1,
            function (Room|Living|Item $a) {
                if ($a instanceof Room) {
                    $list = $a->getItems();
                } elseif ($a instanceof Living) {
                    $list = $a->getInventory();
                } else {
                    $list = $a->getContents();
                }

                return new Vector(array_values($list->getAll()));
            }
        ));

        $env->set('get-level', new CoreFunc('get-level', 'Return the level of the given living entity.', 1, 1,
            fn (Living $a) => $a->getLevel()
        ));

        $env->set('get-living', new CoreFunc('get-living', 'Return all the living entities in the given room as a vector.', 1, 1,
            function (Group|Room $a) {
                if ($a instanceof Group) {
                    return new Vector(array_values($a->getMembers()->getAll()));
                } else {
                    return new Vector(array_values($a->getLiving()->getAll()));
                }
            }
        ));

        $env->set('get-mana', new CoreFunc('get-mana', 'Return the mana points of the given player.', 1, 1,
            fn (Player $a) => $a->getMana()
        ));

        $env->set('get-max-mana', new CoreFunc('get-max-mana', 'Return the maximum mana for the given player.', 1, 1,
            fn (Player $a) => $a->getMaxMana()
        ));

        $env->set('get-mod', new CoreFunc('get-mod', 'Return the modifier (second argument) of the given item or living entity (first argument).', 2, 2,
            function (Item|Living $a, string $mod) {
                if ($a instanceof Item) {
                    return $a->getTemplate()->getMod(Modifier::from($mod));
                } else {
                    return $a->getMod(Modifier::from($mod));
                }
            }
        ));

        $env->set('get-move', new CoreFunc('get-move', 'Return the movement points of the given player.', 1, 1,
            fn (Player $a) => $a->getMove()
        ));

        $env->set('get-max-move', new CoreFunc('get-max-move', 'Return the maximum movement points for the given player.', 1, 1,
            fn (Player $a) => $a->getMaxMove()
        ));

        $env->set('get-name', new CoreFunc('get-name', 'Return the name of the given object.', 1, 1,
            function (BaseObject $a) {
                if ($a instanceof Item || $a instanceof Monster) {
                    return $a->getTemplate()->getName();
                } elseif (method_exists($a, 'getName')) {
                    return $a->getName();
                }

                return null;
            }
        ));

        $env->set('get-a-name', new CoreFunc('get-a-name', "Return the name of the given monster or item with 'a' or 'an' article.", 1, 2,
            fn (Item|Monster $a, int $count = 1) => $a->getTemplate()->getAName($count)
        ));

        $env->set('get-the-name', new CoreFunc('get-the-name', "Return the name of the given monster or item with 'the' article.", 1, 2,
            fn (Item|Monster $a, int $count = 1) => $a->getTemplate()->getTheName($count)
        ));

        $env->set('get-uniq-name', new CoreFunc('get-uniq-name', 'Return the unique name for the given object.', 1, 1,
            fn (BaseObject $a) => $a->getTechnicalName()
        ));

        $env->set('get-num-attacks', new CoreFunc('get-num-attacks', 'Return the number of attacks per round the given living entity performs.', 1, 1,
            fn (Living $a) => $a->getNumAttacks()
        ));

        $env->set('get-room', new CoreFunc('get-room', 'Return the room of the given item or living entity.', 1, 1,
            function (Item|Living|RoomExit $a) {
                if ($a instanceof RoomExit) {
                    return $a->getFrom();
                }

                return $a->getRoom();
            }
        ));

        $env->set('get-exit', new CoreFunc('get-exit', 'Return a single exit for the given room.', 2, 2,
            fn (Room $a, string $dir) => $a->getExit(Direction::from($dir))
        ));

        $env->set('get-exits', new CoreFunc('get-exits', 'Return all the exits for the given room. Give a living entity as optional second argument to only return exits that it can pass.', 1, 2,
            fn (Room $a, ?Living $passableBy = null) => new Hash($a->getExits($passableBy))
        ));

        $env->set('get-zone-rooms', new CoreFunc('get-zone-rooms', 'Return the rooms of the given zone as a vector.', 1, 1,
            fn (Zone $a) => new Vector(array_values($a->getRooms()->getAll()))
        ));

        $env->set('get-sex', new CoreFunc('get-sex', 'Return sex of the given living entity.', 1, 1,
            fn (Living $a) => $a->getSex()->value
        ));

        $env->set('get-size', new CoreFunc('get-size', 'Return the size of the given living entity.', 1, 1,
            fn (Living $a) => $a->getSize()->value
        ));

        $env->set('get-target', new CoreFunc('get-target', 'Return the fight target of the given living entity.', 1, 1,
            fn (Living $a) => $a->getTarget()
        ));

        $env->set('get-terrain', new CoreFunc('get-terrain', 'Return the terrain for the given room.', 1, 1,
            fn (Room $a) => $a->getTerrain()->value
        ));

        $env->set('get-weapon', new CoreFunc('get-weapon', 'Return the equipped weapon of the given living entity.', 1, 1,
            fn (Living $a) => $a->getWeapon()
        ));

        $env->set('get-wearer', new CoreFunc('get-wearer', 'Return the wearer of the given item or null.', 1, 1,
            fn (Item $a) => $a->getWearer()
        ));

        $env->set('get-weight', new CoreFunc('get-weight', 'Return the weight of the given item or living entity.', 1, 1,
            fn (Item|Living $a) => $a->getWeight()
        ));

        $env->set('get-zone', new CoreFunc('get-zone', 'Return the zone of the given room or living entity.', 1, 1,
            function (Living|Room $a) {
                $room = ($a instanceof Room) ? $a : $a->getRoom();
                return $room ? $room->getZone() : null;
            }
        ));
    }

    private function registerSetters(Env $env): void
    {
        $env->set('add-coins', new CoreFunc('add-coins', 'Add the number of coins (second argument) for the given living entity (first argument).', 2, 2,
            fn (Living $a, int $amount) => $a->addCoins($amount)
        ));

        $env->set('add-bank', new CoreFunc('add-bank', 'Add the number of coins (second argument) to the bank account of the given player (first argument).', 2, 2,
            fn (Player $a, int $amount) => $a->addBank($amount)
        ));

        $env->set('add-exp', new CoreFunc('add-exp', 'Add experience points (second argument) to the given player (first argument).', 2, 2,
            fn (Player $a, int $amount) => Experience::gainExperience($a, $amount)
        ));

        // TODO: setters for health, mana, move
    }

    private function registerActions(Env $env): void
    {
        // Movement actions

        $env->set('move', new CoreFunc('move', 'Make the living entity (first argument) move towards the given direction (second argument).', 2, 2,
            fn (Living $living, string $dir) => $this->actionMove->move($living, Direction::from($dir))
        ));

        // Fight actions

        $env->set('attack', new CoreFunc('attack', 'Make the first living entity attack the second.', 2, 3,
            function (Living $living, Living $target, bool $showMessage = true) {
                if ($showMessage) {
                    $this->action->attack($living, $target);
                }
                $this->fight->attack($living, $target);
                return null;
            }
        ));

        $env->set('assist', new CoreFunc('assist', 'Make the first living entity assist the second in combat.', 2, 3,
            function (Living $living, Living $defender, bool $showMessage = true) {
                if ($showMessage) {
                    $this->action->assist($living, $defender);
                }
                $this->fight->attack($living, $defender->getTarget());
                return null;
            }
        ));

        $env->set('damage', new CoreFunc('damage', 'Damage the given living entity (first argument) by given amount (second argument). Optional third argument is the attacker.', 2, 3,
            fn (Living $living, float $amount, ?Living $attacker = null) => $this->fight->damage($living, $amount, $attacker)
        ));

        // TODO: rescue

        $env->set('flee', new CoreFunc('flee', 'Make the given living entity flee from combat.', 1, 1,
            fn (Living $living) => $this->fight->flee($living)
        ));

        // Item actions

        $env->set('discard', new CoreFunc('discard', 'Make the given living entity (first argument) discard the given item (second argument).', 2, 2,
            fn (Living $living, Item $item) => $this->action->discard($living, $item)
        ));

        $env->set('drop', new CoreFunc('drop', 'Make the given living entity (first argument) drop the given item (second argument).', 2, 2,
            fn (Living $living, Item $item) => $this->action->drop($living, $item)
        ));

        $env->set('pickup', new CoreFunc('pickup', 'Make the given living entity (first argument) pick up the given item (second argument) from the current room.', 2, 2,
            fn (Living $living, Item $item) => $this->action->get($living, $item)
        ));

        $env->set('get-from-container', new CoreFunc('get-from-container', 'Make the given living entity (first argument) get the given item (second argument) from the given container (third argument).', 3, 3,
            fn (Living $living, Item $item, Item $container) => $this->action->getFromContainer($living, $item, $container)
        ));

        $env->set('give', new CoreFunc('give', 'Make the given living entity (first argument) give the item (second argument) to the living entity (third argument).', 3, 3,
            fn (Living $source, Item $item, Living $target) => $this->action->give($source, $item, $target)
        ));

        $env->set('give-coins', new CoreFunc('give-coins', 'Make the given living entity (first argument) give amount of coins (second argument) to target living entity (third argument).', 3, 3,
            fn (Living $source, int $amount, Living $target) => $this->action->giveCoins($source, $amount, $target)
        ));

        $env->set('put-in-container', new CoreFunc('put-in-container', 'Make the given living entity (first argument) put the given item (second argument) into the given container (third argument).', 3, 3,
            fn (Living $living, Item $item, Item $container) => $this->action->putInContainer($living, $item, $container)
        ));

        $env->set('remove', new CoreFunc('remove', 'Make the given living entity (first argument) to stop using the given piece of equipment (second argument).', 2, 2,
            fn (Living $living, Item $item) => $this->action->remove($living, $item)
        ));

        $env->set('wear', new CoreFunc('wear', 'Make the given living entity (first argument) wear the given item (second argument) in the given equipment slot (third argument).', 3, 3,
            fn (Living $living, Item $item, string $slot) => $this->action->wear($living, $item, EqSlot::from($slot))
        ));

        // Door actions

        $env->set('open-door', new CoreFunc('open-door', 'Make the given living entity (first argument) open the door in given direction (second argument).', 2, 2,
            fn (Living $living, string $dir) => $this->action->open($living, Direction::from($dir))
        ));

        $env->set('close-door', new CoreFunc('close-door', 'Make the given living entity (first argument) close the door in given direction (second argument).', 2, 2,
            fn (Living $living, string $dir) => $this->action->close($living, Direction::from($dir))
        ));

        $env->set('lock', new CoreFunc('lock', 'Make the given living entity (first argument) lock the door in given direction (second argument).', 2, 2,
            fn (Living $living, string $dir) => $this->action->lock($living, Direction::from($dir))
        ));

        $env->set('unlock', new CoreFunc('unlock', 'Make the given living entity (first argument) unlock the door in given direction (second argument).', 2, 2,
            fn (Living $living, string $dir) => $this->action->unlock($living, Direction::from($dir))
        ));

        // Communication actions

        $env->set('emote', new CoreFunc('emote', 'Display a message to the room of the given living entity.', 2, 2,
            fn (Living $living, string $message) => $this->action->emote($living, $message)
        ));

        $env->set('say', new CoreFunc('say', 'Communicate the given argument in the current room.', 2, 2,
            fn (Living $living, string $message) => $this->action->say($living, $message)
        ));

        $env->set('social', new CoreFunc('social', 'Make the given living entity perform a social action with optional target as third argument.', 2, 3,
            function (Living $living, string $name, ?Living $target = null) {
                $social = $this->socials->findSocial($name);
                if (!$social) {
                    return false;
                }

                if ($target) {
                    $this->socials->actSocialTarget($living, $social, $target);
                } else {
                    $this->socials->actSocial($living, $social);
                }

                return true;
            }
        ));
    }

    private function registerWorldFunctions(Env $env): void
    {
        $env->set('find-faction', new CoreFunc('find-faction', 'Return the faction with the given id.', 1, 1,
            fn (string $id) => $this->lists->getFactions()->get($id)
        ));

        $env->set('find-room', new CoreFunc('find-room', 'Return the room with the given id.', 1, 1,
            fn (int $id) => $this->lists->getRooms()->get($id)
        ));

        $env->set('find-zone', new CoreFunc('find-zone', 'Return the zone with the given id.', 1, 1,
            fn (int $id) => $this->lists->getZones()->get($id)
        ));

        $env->set('reset-zone', new CoreFunc('reset-zone', 'Reset the given zone.', 1, 1,
            fn (Zone $a) => $this->zoneReset->reset($a, false)
        ));

        $env->set('extract', new CoreFunc('extract', 'Destroy the given instance of item or monster.', 1, 1,
            function (Item|Monster $a) {
                if ($a instanceof Item) {
                    $this->world->extractItem($a);
                } else {
                    $this->world->extractLiving($a);
                }
                return null;
            }
        ));

        $env->set('load-item', new CoreFunc('load-item', 'Create an instance of the given item (first argument) and place it in room or living entity (second argument).', 2, 2,
            function (int $id, Room|Living $target) {
                $template = $this->lists->getItemTemplates()->get($id);
                if (!$template) {
                    return null;
                }

                if ($target instanceof Room) {
                    $this->world->loadItemToRoom($template, $target);
                } else {
                    $this->world->loadItemToInventory($template, $target);
                }

                return $item;
            }
        ));

        $env->set('load-monster', new CoreFunc('load-monster', 'Create an instance of the given monster (first argument) and place it in the given room (second argument).', 2, 2,
            function (int $id, Room $room) {
                $template = $this->lists->getMonsterTemplates()->get($id);
                if (!$template) {
                    return null;
                }

                return $this->world->loadMonster($template, $room);
            }
        ));

        $env->set('item-to-container', new CoreFunc('item-to-container', 'Put the given item (first argument) inside the given container (second argument).', 2, 2,
            fn (Item $item, Item $container) => $this->world->itemToContainer($item, $container)
        ));

        $env->set('item-to-eq', new CoreFunc('item-to-eq', 'Wear the given item (first argument) on the living entity (second argument) in the specified equipment slot (third argument).', 3, 3,
            fn (Item $item, Living $living, string $slot) => $this->world->itemToEquipment($item, $living, EqSlot::from($slot))
        ));

        $env->set('item-to-inv', new CoreFunc('item-to-inv', 'Place the given item (first argument) in the inventory of the living entity (second argument).', 2, 2,
            fn (Item $item, Living $living) => $this->world->itemToInventory($item, $living)
        ));

        $env->set('item-to-room', new CoreFunc('item-to-room', 'Place the given item (first argument) in the given room (second argument).', 2, 2,
            fn (Item $item, Room $room) => $this->world->itemToRoom($item, $room)
        ));

        $env->set('living-to-room', new CoreFunc('living-to-room', 'Place the living entity (first argument) in the given room (second argument).', 2, 2,
            fn (Living $living, Room $room) => $this->world->livingToRoom($living, $room)
        ));
    }

    private function registerMiscFunctions(Env $env)
    {
        // Access local environment

        $env->set('get-lisp-env', new CoreFunc('get-lisp-env', 'Return the Lisp environment of the given object.', 1, 1,
            fn (BaseObject $a) => $a->getLispEnv()
        ));

        $env->set('get-my-env', new CoreFunc('get-my-env', 'Return a value from the Lisp environment of the given object.', 2, 2,
            function (BaseObject $obj, string $key) {
                $myEnv = $obj->getLispEnv()->getData();
                return $myEnv[$key] ?? null;
            }
        ));

        $env->set('set-my-env', new CoreFunc('set-my-env', 'Set a value to the Lisp environment of the given object.', 3, 3,
            function (BaseObject $obj, string $key, $value) {
                $obj->getLispEnv()->set($key, $value);
            }
        ));

        // Act messages

        $env->set('act-char', new CoreFunc('act-char', "Send 'act' message to character.", 2, 4,
            fn (string $txt, Living $ch, ?Item $obj = null, $victObj = null) =>
                $this->act->toChar($txt, $ch, $obj, $victObj)
        ));

        $env->set('act-vict', new CoreFunc('act-vict', "Send 'act' message to victim.", 5, 5,
            fn (string $txt, bool $hide, Living $ch, ?Item $obj, Living $victObj) =>
                $this->act->toVict($txt, $hide, $ch, $obj, $victObj)
        ));

        $env->set('act-room', new CoreFunc('act-room', "Send 'act' message to room.", 3, 6,
            fn (string $txt, bool $hide, Living $ch, ?Item $obj = null, $victObj = null, bool $hideVict = false) =>
                $this->act->toRoom($txt, $hide, $ch, $obj, $victObj, $hideVict)
        ));

        // Random functions

        $env->set('percent', new CoreFunc('percent', 'Random function that has the given chance in percent to return true.', 1, 1,
            fn (int $val) => Random::percent($val)
        ));

        $env->set('permil', new CoreFunc('permil', 'Random function that has the given chance in permil to return true.', 1, 1,
            fn (int $val) => Random::permil($val)
        ));

        $env->set('rand-from-seq', new CoreFunc('rand-from-seq', 'Choose a random item from the given sequence.', 1, 1,
            function (Seq $seq) {
                // Checks for empty also
                return Random::fromArray($seq->getData());
            }
        ));

        // Logging functions

        $env->set('log-debug', new CoreFunc('log-debug', 'Write a debug message to log.', 1, -1,
            function (string $message, ...$args) {
                Log::debug(sprintf($message, ...$args));
                return null;
            }
        ));

        $env->set('log-info', new CoreFunc('log-info', 'Write a message to log.', 1, -1,
            function (string $message, ...$args) {
                Log::info(sprintf($message, ...$args));
                return null;
            }
        ));

        $env->set('log-warn', new CoreFunc('log-warn', 'Write a warning to log.', 1, -1,
            function (string $message, ...$args) {
                Log::warn(sprintf($message, ...$args));
                return null;
            }
        ));

        $env->set('log-error', new CoreFunc('log-error', 'Write an error to log.', 1, -1,
            function (string $message, ...$args) {
                Log::error(sprintf($message, ...$args));
                return null;
            }
        ));

        // Player output

        $env->set('pl-out', new CoreFunc('pl-out', 'Write the given string to the output of the player.', 2, -1,
            fn (Player $a, string $txt, ...$args) => $a->out($txt, ...$args)
        ));

        $env->set('pl-outln', new CoreFunc('pl-outln', 'Write the given string with linebreak to the output of the player.', 2, -1,
            fn (Player $a, string $txt, ...$args) => $a->outln($txt, ...$args)
        ));

        $env->set('pl-outpr', new CoreFunc('pl-outpr', 'Write the given string to the output of the player, splitting it into multiple lines if required.', 2, -1,
            fn (Player $a, string $txt, ...$args) => $a->outpr($txt, ...$args)
        ));

        $env->set('pl-out-table', new CoreFunc('pl-out-table', 'Make a table of the given sequence and write it to the output of the player.', 2, 2,
            fn (Player $a, Seq $seq) => $a->outWordTable($seq->getData())
        ));
    }

    private function registerHelpers(Env $env): void
    {
        // Register some helper functions for common things because
        // right now the Lisp language is slow so it is more efficient
        // to execute more code on the PHP side.

        $env->set('find-living', new CoreFunc('find-living', 'Find living entities from current room according to given filters.', 1, -1,
            function (Living $living, ...$filters) {
                $results = [];

                $filterIds = null;
                foreach ($filters as $f) {
                    if ($f instanceof Seq) {
                        $filterIds = $f->getData();
                        break;
                    }
                }

                foreach ($living->getRoom()->getLiving()->getAll() as $other) {
                    // Skip self and invisible
                    if ($living === $other || !$living->canSee($other)) {
                        continue;
                    }

                    // Type
                    if (in_array('player', $filters) && !$other->isPlayer()) {
                        continue;
                    }
                    if (in_array('monster', $filters) && !$other->isMonster()) {
                        continue;
                    }

                    // Fighters
                    if (in_array('fighting', $filters) && !$other->getTarget()) {
                        continue;
                    }
                    if (in_array('not-fighting', $filters) && $other->getTarget()) {
                        continue;
                    }
                    if (in_array('assist', $filters) && !$living->shouldAssist($other)) {
                        continue;
                    }

                    // Filters for monster
                    if ($other->isMonster()) {
                        $template = $other->getTemplate();

                        // Filter by id
                        if ($filterIds && !in_array($template->getId(), $filterIds)) {
                            continue;
                        }

                        // Flags
                        if (in_array('vermin', $filters) &&
                            !$template->hasFlag(MonsterFlag::Vermin)) {
                            continue;
                        }
                    }

                    $results[] = $other;
                }

                return new Vector($results);
            }
        ));

        $env->set('find-items', new CoreFunc('find-items', 'Find items from the given list according to given filters.', 2, -1,
            function (Living $living, Seq $list, ...$filters) {
                $results = [];

                $filterIds = null;
                foreach ($filters as $f) {
                    if ($f instanceof Seq) {
                        $filterIds = $f->getData();
                        break;
                    }
                }

                foreach ($list->getData() as $item) {
                    $template = $item->getTemplate();

                    // Skip invisible
                    if (!$living->canSeeItem($item)) {
                        continue;
                    }

                    // Filter by id
                    if ($filterIds && !in_array($template->getId(), $filterIds)) {
                        continue;
                    }

                    // Flags
                    if (in_array('corpse', $filters) &&
                        !$template->hasFlag(ItemFlag::MonsterCorpse) &&
                        !$template->hasFlag(ItemFlag::PlayerCorpse)) {
                        continue;
                    }
                    if (in_array('mcorpse', $filters) &&
                        !$template->hasFlag(ItemFlag::MonsterCorpse)) {
                        continue;
                    }
                    if (in_array('pcorpse', $filters) &&
                        !$template->hasFlag(ItemFlag::PlayerCorpse)) {
                        continue;
                    }

                    // Types
                    if (in_array('armor', $filters) && !$item->isArmor()) {
                        continue;
                    }
                    if (in_array('weapon', $filters) && !$item->isWeapon()) {
                        continue;
                    }
                    if (in_array('container', $filters) && !$item->isContainer()) {
                        continue;
                    }
                    if (in_array('equipment', $filters) && !$item->isEquipment()) {
                        continue;
                    }
                    if (in_array('useful', $filters) && !$item->isUseful()) {
                        continue;
                    }

                    $results[] = $item;
                }

                return new Vector($results);
            }
        ));
    }
}
