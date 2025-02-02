<?php
/**
 * Gauntlet MUD - Stats command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Info;

use Gauntlet\Experience;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Enum\Modifier;
use Gauntlet\Enum\MoneyType;
use Gauntlet\Util\Config;
use Gauntlet\Util\Currency;
use Gauntlet\Util\Input;

class Stats extends BaseCommand
{
    public const STATS = 'stats';
    public const COIN = 'coin';

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($subcmd == self::COIN) {
            $this->showCoins($player);
            return;
        }

        $player->outln('You are %s %s human %s %s (level %d).', $player->getSize()->value,
            $player->getSex()->name(), $player->getClass()->value, $player->getName(),
            $player->getLevel());

        $player->outln('Your attributes are %d Str, %d Dex, %d Int and %d Con.',
            $player->getStr(), $player->getDex(), $player->getInt(), $player->getCon());

        $trains = $player->getRemainingTraining();
        if ($trains > 0) {
            $player->outln('You have %d attribute training points remaining.', $trains);
        }
        $skillPoints = $player->getRemainingSkillPoints();
        if ($skillPoints > 0) {
            $player->outln('You have %d %spoints remaining.', $skillPoints, $player->getClass()->spellSkill());
        }

        $expLine = 'You have ' . $player->getExperience() . ' experience';
        if ($player->getLevel() < MAX_LEVEL) {
            $nextLvl = Experience::getPlayerExpToLevel($player->getLevel() + 1);
            $remaining = $nextLvl - $player->getExperience();
            $expLine .= ' and need ' . $remaining . ' to reach next level.';
        } else {
            $expLine .= '.';
        }
        $player->outln($expLine);

        $player->outln('You have %.0f/%.0f health, %.0f/%.0f mana and %.0f/%.0f movement.',
            $player->getHealth(), $player->getMaxHealth(),
            $player->getMana(), $player->getMaxMana(),
            $player->getMove(), $player->getMaxMove());

        $player->outln('You are carrying %.0f/%.0f kg of items and equipment.',
            $player->getCarriedWeight(), $player->getCarryingCapacity());
        if ($player->isEncumbered()) {
            $player->outln('Carrying this extra weight is making you encumbered.');
        }

        $this->showCoins($player);

        $toHit = $player->bonusToHit();
        if ($toHit != 0) {
            $player->outln('You have %d%% increased chance to hit your target.', $toHit);
        }

        $toDodge = $player->bonusToDodge();
        if ($toDodge != 0) {
            $player->outln('You have %d%% increased chance to dodge enemy attacks.', $toDodge);
        }

        $armor = $player->getMod(Modifier::Armor);
        if ($armor != 0) {
            $player->outln('Your armor reduces incoming damage by %.1f.', $armor);
        }

        $player->outln(
            'Your attacks do %.1f to %.1f %s damage.',
            $player->getMinDamage() + $player->getBonusDamage(),
            $player->getMaxDamage() + $player->getBonusDamage(),
            $player->getDamageType()->value
        );

        if ($player->getGroup()) {
            if ($player->getGroup()->getLeader() === $player) {
                $player->outln('You are the leader of a party.');
            } else {
                $player->outln('You are a member of a party.');
            }
        }
    }

    public function getDescription(?string $subcmd): string
    {
        if ($subcmd == self::STATS) {
            return 'Display information and statistics about your character.';
        } else {
            if (Config::moneyType() == MoneyType::Credits) {
                return 'Display your credit balance.';
            } else {
                return "Show how many coins you are carrying.";
            }
        }
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            '',
        ];
    }

    public function getSeeAlso(?string $subcmd): array
    {
        if ($subcmd == self::COIN) {
            $result = ['balance'];
            if (Config::moneyType() == MoneyType::Coins) {
                $result[] = 'currency';
            }
            return $result;
        }

        return parent::getSeeAlso($subcmd);
    }

    private function showCoins(Player $player): void
    {
        if ($player->getCoins() == 0) {
            if (Config::moneyType() == MoneyType::Credits) {
                $player->outln('You credit balance is zero.');
            } else {
                $player->outln('You are not carrying any coins.');
            }
        } else {
            $format = Currency::format($player->getCoins(), false);
            if (Config::moneyType() == MoneyType::Credits) {
                $player->outln('You have %s credits.', $format);
            } else {
                $player->outln('You are carrying %s coins.', $format);
            }
        }
    }
}
