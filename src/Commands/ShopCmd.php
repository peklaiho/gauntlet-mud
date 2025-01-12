<?php
/**
 * Gauntlet MUD - Shop command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands;

use Gauntlet\Act;
use Gauntlet\Lists;
use Gauntlet\Player;
use Gauntlet\World;
use Gauntlet\Util\Config;
use Gauntlet\Util\Currency;
use Gauntlet\Util\Input;
use Gauntlet\Util\ItemFinder;
use Gauntlet\Util\Log;
use Gauntlet\Util\TableFormatter;

class ShopCmd extends BaseCommand
{
    public const LIST = 'list';
    public const BUY = 'buy';
    public const SELL = 'sell';

    public function __construct(
        protected Act $act,
        protected ItemFinder $itemFinder,
        protected World $world,
        protected Lists $lists
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if (!$player->canSeeRoom()) {
            $player->outln(MESSAGE_DARK);
            return;
        }

        $shop = $this->lists->getShops()->get($player->getRoom()->getId());

        if (!$shop) {
            $message = match($subcmd) {
                self::LIST => 'There does not seem to be a shop here.',
                self::BUY => 'You must be in a shop to buy items.',
                self::SELL => 'You must be in a shop to sell items.'
            };
            $player->outln($message);
            return;
        }

        $moneyType = Config::moneyType();

        if ($subcmd == self::LIST) {
            if (empty($shop->getItemIds())) {
                $player->outln('There does not appear to be any items for sale.');
            } else {
                $rows = [];

                foreach ($shop->getItemIds() as $index => $itemId) {
                    $itemTemplate = $this->lists->getItemTemplates()->get($itemId);
                    $rows[] = [
                        $index + 1,
                        $itemTemplate->getAName(),
                        Currency::format($itemTemplate->getCost(), true)
                    ];
                }

                $rows = TableFormatter::format($rows, ['#', 'Name', 'Cost'], [1]);

                foreach ($rows as $row) {
                    $player->outln($row);
                }
            }
        } elseif ($subcmd == self::BUY) {
            if ($input->empty()) {
                $player->outln("Which item you want to buy?");
            } else {
                $target = $input->get(0);
                $itemTemplate = null;

                if (filter_var($target, FILTER_VALIDATE_INT)) {
                    $itemId = $shop->getItemIds()[$target - 1] ?? null;
                    if ($itemId) {
                        $itemTemplate = $this->lists->getItemTemplates()->get($itemId);
                    }
                } else {
                    foreach ($shop->getItemIds() as $itemId) {
                        if ($this->lists->getItemTemplates()->get($itemId)->hasKeyword($target)) {
                            $itemTemplate = $this->lists->getItemTemplates()->get($itemId);
                            break;
                        }
                    }
                }

                if ($itemTemplate) {
                    if ($itemTemplate->getCost() > $player->getCoins()) {
                        $player->outln('You cannot afford it.');
                    } else {
                        // Create item
                        $item = $this->world->loadItemToInventory($itemTemplate, $player);

                        // Take money
                        $player->addCoins(-$itemTemplate->getCost());

                        // Show messages
                        $format = Currency::format($itemTemplate->getCost(), false);
                        $this->act->toChar("You buy @p for $format @+.", $player, $item, $moneyType->value);
                        $this->act->toRoom("@a buys @o.", true, $player, $item);

                        // Log the transaction
                        Log::money($player->getName() . ' buys ' . $itemTemplate->getAName() . ' for ' .
                            $itemTemplate->getCost() . ' ' . $moneyType->value . '.');
                    }
                } else {
                    $player->outln('That item is not sold here. Try again.');
                }
            }
        } else {
            if ($input->empty()) {
                $player->outln("Which item you want to sell?");
            } else {
                $lists = [$player->getInventory()];
                $item = $this->itemFinder->find($player, $input->get(0), $lists);

                if ($item) {
                    $value = $shop->paysForItem($item);
                    if ($value > 0) {
                        // Prevent accidentally selling containers with contents inside
                        if ($item->getContents()->empty()) {
                            // Give money
                            $player->addCoins($value);

                            // Show messages
                            $format = Currency::format($value, false);
                            $this->act->toChar("You sell @p for $format @+.", $player, $item, $moneyType->value);
                            $this->act->toRoom("@a sells @o.", true, $player, $item);

                            // Extract item
                            $this->world->extractItem($item);

                            // Log the transaction
                            Log::money($player->getName() . ' sells ' . $item->getTemplate()->getAName() . ' for ' .
                                $value . ' ' . $moneyType->value . '.');
                        } else {
                            $player->outln('There seems to be something inside. You should empty it first.');
                        }
                    } else {
                        $player->outln('The shop does not want to buy that.');
                    }
                } else {
                    $player->outln('You are not carrying anything by that name.');
                }
            }
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return match($subcmd) {
            self::LIST => 'List items available for purchase.',
            self::BUY => "Buy an item from a shop. Give item name or the number shown in the # column as argument. For example 'buy 3' to buy the third item from the list.",
            self::SELL => 'Sell an item to a shop.',
        };
    }

    public function getUsage(?string $subcmd): array
    {
        return match($subcmd) {
            self::LIST => [''],
            self::BUY => ['<item>'],
            self::SELL => ['<item>'],
        };
    }

    public function getSeeAlso(?string $subcmd): array
    {
        return match($subcmd) {
            self::LIST => ['buy', 'sell', 'shops'],
            self::BUY => ['list', 'sell', 'shops'],
            self::SELL => ['buy', 'list', 'shops'],
        };
    }
}
