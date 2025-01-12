<?php
/**
 * Gauntlet MUD - Give command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Items;

use Gauntlet\Act;
use Gauntlet\Action;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Enum\MoneyType;
use Gauntlet\Util\Config;
use Gauntlet\Util\Currency;
use Gauntlet\Util\Input;
use Gauntlet\Util\ItemFinder;
use Gauntlet\Util\LivingFinder;
use Gauntlet\Util\Log;

class Give extends BaseCommand
{
    public function __construct(
        protected ItemFinder $itemFinder,
        protected Action $action,
        protected Act $act
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($input->count() < 2) {
            $player->outln('Give what to whom?');
            return;
        }

        $itemName = $input->get(0);
        $targetName = $input->get(1);

        // Skip over 'to' if it was given
        if ($input->count() >= 3 && strcasecmp($targetName, 'to') == 0) {
            $targetName = $input->get(2);
        }

        $lists = [$player->getRoom()->getLiving()];
        $target = (new LivingFinder($player, $lists))
            ->excludeSelf()
            ->find($targetName);

        if (!$target) {
            $player->outln(MESSAGE_NOONE);
            return;
        }

        // See first if we are giving coins
        if (Config::moneyType() == MoneyType::Coins) {
            $coins = Currency::parse($itemName);
            if ($coins) {
                if ($coins > $player->getCoins()) {
                    $player->outln('You do not have enough coins for that amount.');
                } else {
                    // Log the transaction
                    $logMessage = $player->getName() . ' gives ' . $coins . ' coins to ' .
                        $target->getName() . ' in room ' . $player->getRoom()->getTemplate()->getId() . '.';
                    Log::money($logMessage);
                    if ($player->getAdminLevel()) {
                        // Add to admin log also
                        Log::admin($logMessage);
                    }

                    $this->action->giveCoins($player, $coins, $target);
                }
                return;
            }
        }

        // We are giving an item
        $lists = [$player->getInventory()];
        $item = $this->itemFinder->find($player, $itemName, $lists);

        if (!$item) {
            $player->outln('You are not carrying anything by that name.');
        } elseif ($target->isPlayer() && !$target->canCarry($item, false)) {
            $this->act->toChar('@E cannot carry that much weight.', $player, null, $target);
        } else {
            if ($player->getAdminLevel()) {
                Log::admin($player->getName() . ' gives ' . $item->getTemplate()->getAName() . ' to ' .
                    $target->getName() . ' in room ' . $player->getRoom()->getTemplate()->getId() . '.');
            }
            $this->action->give($player, $item, $target);
        }
    }

    public function getDescription(?string $subcmd): string
    {
        $result = 'Give an item to NPC or another player.';

        if (Config::moneyType() == MoneyType::Coins) {
            $result .= " You can also give coins by specifying an amount followed by 'g', 's' or 'c' for gold, silver or copper respectively. For example '1g20s50c' stands for 1 gold, 20 silver and 50 copper coins.";
        }

        return $result;
    }

    public function getUsage(?string $subcmd): array
    {
        $result = [
            "<item> ['to'] <target>",
        ];

        if (Config::moneyType() == MoneyType::Coins) {
            $result[] = "<currency> ['to'] <target>";
        }

        return $result;
    }

    public function getSeeAlso(?string $subcmd): array
    {
        $result = ['inventory', 'items'];

        if (Config::moneyType() == MoneyType::Coins) {
            $result[] = 'currency';
        }

        return $result;
    }
}
