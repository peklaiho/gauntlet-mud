<?php
/**
 * Gauntlet MUD - Bank command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands;

use Gauntlet\Act;
use Gauntlet\Player;
use Gauntlet\Enum\MoneyType;
use Gauntlet\Enum\RoomFlag;
use Gauntlet\Util\Config;
use Gauntlet\Util\Currency;
use Gauntlet\Util\Input;
use Gauntlet\Util\Log;

class Bank extends BaseCommand
{
    public const BALANCE = 'balance';
    public const DEPOSIT = 'deposit';
    public const WITHDRAW = 'withdraw';

    public function __construct(
        protected Act $act
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        $moneyType = Config::moneyType()->value;

        if (!$player->canSeeRoom()) {
            $player->outln(MESSAGE_DARK);
            return;
        } elseif (!$player->getRoom()->getTemplate()->hasFlag(RoomFlag::Bank)) {
            $message = match($subcmd) {
                self::BALANCE => 'You must be in a bank to check your balance.',
                self::DEPOSIT => "You must be in a bank to deposit $moneyType.",
                self::WITHDRAW => "You must be in a bank to withdraw $moneyType."
            };
            $player->outln($message);
            return;
        }

        $readAmount = function () use ($input, $player) {
            $amount = Currency::parse($input->getWholeArgument());

            if (!$amount) {
                $player->outln('Invalid amount. Try again.');
                return null;
            } elseif ($amount < 100) {
                $player->outln('The amount is below the minimum accepted by the bank.');
                return null;
            }

            return $amount;
        };

        $takeFee = function ($amount) use ($player, $moneyType) {
            static $fee = 5;
            $format = Currency::format($fee, false);
            $player->outln("The bank deducts a fee of $format $moneyType for the transaction.");
            return $amount - $fee;
        };

        if ($subcmd == self::BALANCE) {
            if ($player->getBank() == 0) {
                $player->outln('You have no balance in your account.');
            } else {
                $format = Currency::format($player->getBank(), false);
                $player->outln("Your balance is $format $moneyType.");
            }
        } elseif ($subcmd == self::DEPOSIT) {
            if ($input->empty()) {
                $player->outln('What amount do you wish to deposit?');
            } else {
                $amount = $readAmount();
                if ($amount) {
                    if ($amount > $player->getCoins()) {
                        $player->outln("You do not have enough $moneyType for that amount.");
                    } else {
                        $player->addCoins(-$amount);
                        $amount = $takeFee($amount);
                        $player->addBank($amount);

                        $format = Currency::format($amount, false);
                        $player->outln("You deposit $format $moneyType.");
                        $this->act->toRoom('@a makes a deposit.', true, $player);

                        Log::money($player->getName() . ' deposits ' . $amount . ' ' . $moneyType . '.');
                    }
                }
            }
        } else {
            if ($input->empty()) {
                $player->outln('What amount do you wish to withdraw?');
            } else {
                $amount = $readAmount();
                if ($amount) {
                    if ($amount > $player->getBank()) {
                        $player->outln('You do not have enough balance for that amount.');
                    } else {
                        $player->addBank(-$amount);
                        $amount = $takeFee($amount);
                        $player->addCoins($amount);

                        $format = Currency::format($amount, false);
                        $player->outln("You withdraw $format $moneyType.");
                        $this->act->toRoom('@a makes a withdrawal.', true, $player);

                        Log::money($player->getName() . ' withdraws ' . $amount . ' ' . $moneyType . '.');
                    }
                }
            }
        }
    }

    public function getDescription(?string $subcmd): string
    {
        $moneyType = Config::moneyType()->value;

        return match($subcmd) {
            self::BALANCE => 'Show the balance of your bank account.',
            self::DEPOSIT => "Deposit $moneyType into your bank account.",
            self::WITHDRAW => "Withdraw $moneyType from your bank account.",
        };
    }

    public function getUsage(?string $subcmd): array
    {
        return match($subcmd) {
            self::BALANCE => [''],
            self::DEPOSIT => ['<amount>'],
            self::WITHDRAW => ['<amount>'],
        };
    }

    public function getSeeAlso(?string $subcmd): array
    {
        $result = match($subcmd) {
            self::BALANCE => ['deposit', 'withdraw'],
            self::DEPOSIT => ['balance', 'withdraw'],
            self::WITHDRAW => ['balance', 'deposit'],
        };

        if (Config::moneyType() == MoneyType::Coins) {
            $result[] = 'currency';
        }

        return $result;
    }
}
