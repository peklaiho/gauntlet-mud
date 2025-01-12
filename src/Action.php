<?php
/**
 * Gauntlet MUD - Common actions
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use RuntimeException;

use Gauntlet\Enum\Direction;
use Gauntlet\Enum\EqSlot;
use Gauntlet\Enum\ItemFlag;
use Gauntlet\Enum\MoneyType;
use Gauntlet\Util\Config;
use Gauntlet\Util\Currency;
use Gauntlet\Util\Log;

class Action
{
    public function __construct(
        protected World $world,
        protected Act $act
    ) {

    }

    public function attack(Living $living, Living $target): void
    {
        $this->act->toChar("You attack @T.", $living, null, $target);
        $this->act->toVict('@a attacks you!', false, $living, null, $target);
        $this->act->toRoom('@a attacks @A!', false, $living, null, $target, true);
    }

    public function assist(Living $living, Living $target): void
    {
        $this->act->toChar("You rush to aid @T.", $living, null, $target);
        $this->act->toVict('@a rushes to your aid!', false, $living, null, $target);
        $this->act->toRoom('@a rushes to aid @A!', false, $living, null, $target, true);
    }

    public function rescue(Living $living, Living $target): void
    {
        $this->act->toChar("You jump in to rescue @T.", $living, null, $target);
        $this->act->toVict('@a jumps in and rescues you!', false, $living, null, $target);
        $this->act->toRoom('@a jumps in to rescue @A!', false, $living, null, $target, true);
    }

    public function discard(Living $living, Item $item): void
    {
        $encState = $this->getEncumberance($living);

        $this->act->toChar("You discard @p.", $living, $item);
        $this->act->toRoom("@a discards @o.", true, $living, $item);
        $this->world->extractItem($item);

        $this->reportEncumberance($living, $encState);
    }

    public function drop(Living $living, Item $item): void
    {
        $encState = $this->getEncumberance($living);

        $this->act->toChar("You drop @p.", $living, $item);
        $this->world->itemToRoom($item, $living->getRoom());
        $this->act->toRoom("@a drops @o.", true, $living, $item);

        $this->reportEncumberance($living, $encState);
    }

    public function emote(Living $living, string $message): void
    {
        // Display exactly same message to the actor
        $this->act->toChar("@a $message", $living);
        $this->act->toRoom("@a $message", false, $living);
    }

    public function get(Living $living, Item $item): void
    {
        if ($item->getTemplate()->hasFlag(ItemFlag::PlayerCorpse)) {
            Log::info($living->getName() . ' gets ' . $item->getTemplate()->getName() .
                ' in room ' . $living->getRoom()->getTemplate()->getId() . '.');
        }

        $encState = $this->getEncumberance($living);

        $this->act->toRoom("@a gets @o.", true, $living, $item);
        $this->world->itemToInventory($item, $living);
        $this->act->toChar("You get @p.", $living, $item);

        $this->reportEncumberance($living, $encState);
    }

    public function getFromContainer(Living $living, Item $item, Item $container): void
    {
        if ($container->getTemplate()->hasFlag(ItemFlag::PlayerCorpse)) {
            Log::info($living->getName() . ' gets ' . $item->getTemplate()->getName() .
                ' from ' . $container->getTemplate()->getName() .
                ' in room ' . $living->getRoom()->getTemplate()->getId() . '.');
        }

        $encState = $this->getEncumberance($living);

        $this->act->toRoom("@a gets @o from @O.", true, $living, $item, $container);
        $this->world->itemToInventory($item, $living);
        $this->act->toChar("You get @p from @P.", $living, $item, $container);

        $this->reportEncumberance($living, $encState);
    }

    public function give(Living $source, Item $item, Living $target): void
    {
        $encStateSource = $this->getEncumberance($source);
        $encStateTarget = $this->getEncumberance($target);

        $this->act->toChar('You give @p to @T.', $source, $item, $target);
        $this->world->itemToInventory($item, $target);
        $this->act->toVict('@a gives you @o.', false, $source, $item, $target);
        $this->act->toRoom('@a gives @o to @A.', true, $source, $item, $target, true);

        $this->reportEncumberance($source, $encStateSource);
        $this->reportEncumberance($target, $encStateTarget);
    }

    public function giveCoins(Living $source, int $amount, Living $target): void
    {
        $source->addCoins(-$amount);
        $target->addCoins($amount);

        $format = Currency::format($amount, false);

        if (Config::moneyType() == MoneyType::Credits) {
            $this->act->toChar("You transfer $format credits to @T.", $source, null, $target);
            $this->act->toVict("@a transfers you $format credits.", false, $source, null, $target);
            $this->act->toRoom('@a transfers some credits to @A.', true, $source, null, $target, true);
        } else {
            $this->act->toChar("You give $format coins to @T.", $source, null, $target);
            $this->act->toVict("@a gives you $format coins.", false, $source, null, $target);
            $this->act->toRoom('@a gives some coins to @A.', true, $source, null, $target, true);
        }
    }

    public function putInContainer(Living $living, Item $item, Item $container): void
    {
        $encState = $this->getEncumberance($living);

        $this->act->toChar('You put @p inside @P.', $living, $item, $container);
        $this->world->itemToContainer($item, $container);
        $this->act->toRoom('@a puts @o inside @O.', true, $living, $item, $container);

        $this->reportEncumberance($living, $encState);
    }

    // Door commands
    public function open(Living $living, Direction $dir): RoomExit
    {
        return $this->doorCmd($living, $dir, 'open');
    }

    public function close(Living $living, Direction $dir): RoomExit
    {
        return $this->doorCmd($living, $dir, 'close');
    }

    public function lock(Living $living, Direction $dir): RoomExit
    {
        return $this->doorCmd($living, $dir, 'lock');
    }

    public function unlock(Living $living, Direction $dir): RoomExit
    {
        return $this->doorCmd($living, $dir, 'unlock');
    }

    public function say(Living $living, string $message): void
    {
        $verb = 'say';

        if (str_ends_with($message, '!')) {
            $verb = 'exclaim';
        } elseif (str_ends_with($message, '?')) {
            $verb = 'ask';
        }

        $str = sprintf("You %s, '%s'", $verb, $message);
        $this->act->toChar($str, $living);

        $str = sprintf("@a %ss, '%s'", $verb, $message);
        $this->act->toRoom($str, false, $living);
    }

    public function remove(Living $living, Item $item): void
    {
        $slot = $living->findCurrentSlot($item);

        if (!$slot) {
            throw new RuntimeException('Attempt to remove worn item but equipment slot not found.');
        }

        $messages = $slot->removeMessage();

        $this->world->itemToInventory($item, $living);
        $this->act->toChar($messages[0], $living, $item);
        $this->act->toRoom($messages[1], true, $living, $item);
    }

    public function wear(Living $living, Item $item, EqSlot $slot): void
    {
        $messages = $slot->wearMessage();

        $this->world->itemToEquipment($item, $living, $slot);
        $this->act->toChar($messages[0], $living, $item);
        $this->act->toRoom($messages[1], true, $living, $item);
    }

    private function getEncumberance(Living $living): bool
    {
        if ($living->isPlayer()) {
            return $living->isEncumbered();
        }

        return false;
    }

    private function reportEncumberance(Living $living, bool $oldState): void
    {
        if ($living->isPlayer() && $oldState != $living->isEncumbered()) {
            if ($living->isEncumbered()) {
                $living->outln('You are now encumbered.');
            } else {
                $living->outln('You are no longer encumbered.');
            }
        }
    }

    private function doorCmd(Living $living, Direction $dir, string $type): RoomExit
    {
        $exit = $living->getRoom()->getExit($dir);

        $oppRoom = $exit->getTo();
        $oppExit = $oppRoom->getExit($dir->opposite());

        switch ($type) {
            case 'open':
                $exit->setClosed(false);
                $this->act->toChar("You open the {$exit->getTemplate()->getDoorName()}.", $living);
                $this->act->toRoom("@a opens the {$exit->getTemplate()->getDoorName()}.", false, $living);
                if ($oppExit) {
                    $oppExit->setClosed(false);
                    $this->act->toList("The {$oppExit->getTemplate()->getDoorName()} is opened from the other side.", false, $oppRoom->getLiving(), $living);
                }
                break;

            case 'close':
                $exit->setClosed(true);
                $this->act->toChar("You close the {$exit->getTemplate()->getDoorName()}.", $living);
                $this->act->toRoom("@a closes the {$exit->getTemplate()->getDoorName()}.", false, $living);
                if ($oppExit) {
                    $oppExit->setClosed(true);
                    $this->act->toList("The {$oppExit->getTemplate()->getDoorName()} is closed from the other side.", false, $oppRoom->getLiving(), $living);
                }
                break;

            case 'lock':
                $exit->setLocked(true);
                $this->act->toChar("You lock the {$exit->getTemplate()->getDoorName()}.", $living);
                $this->act->toRoom("@a locks the {$exit->getTemplate()->getDoorName()}.", false, $living);
                if ($oppExit) {
                    $oppExit->setLocked(true);
                    $this->act->toList("The {$oppExit->getTemplate()->getDoorName()} is locked from the other side.", false, $oppRoom->getLiving(), $living);
                }
                break;

            case 'unlock':
                $exit->setLocked(false);
                $this->act->toChar("You unlock the {$exit->getTemplate()->getDoorName()}.", $living);
                $this->act->toRoom("@a unlocks the {$exit->getTemplate()->getDoorName()}.", false, $living);
                if ($oppExit) {
                    $oppExit->setLocked(false);
                    $this->act->toList("The {$oppExit->getTemplate()->getDoorName()} is unlocked from the other side.", false, $oppRoom->getLiving(), $living);
                }
                break;
        }

        return $exit;
    }
}
