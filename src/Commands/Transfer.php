<?php
/**
 * Gauntlet MUD - Transfer command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands;

use Gauntlet\Action;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Currency;
use Gauntlet\Util\Input;
use Gauntlet\Util\LivingFinder;
use Gauntlet\Util\Log;

class Transfer extends BaseCommand
{
    public function __construct(
        protected Action $action
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($input->count() < 2) {
            $player->outln('Transfer how much and to whom?');
            return;
        }

        $amount = Currency::parse($input->get(0));

        if (!$amount) {
            $player->outln('Invalid amount.');
            return;
        } elseif ($amount > $player->getCoins()) {
            $player->outln('You do not have enough credits for that amount.');
            return;
        }

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

        // Log the transaction
        $logMessage = $player->getName() . ' transfers ' . $amount .
            ' credits to ' . $target->getName() . ' in room ' .
            $player->getRoom()->getTemplate()->getId() . '.';

        Log::money($logMessage);
        if ($player->getAdminLevel()) {
            // Add to admin log also
            Log::admin($logMessage);
        }

        $this->action->giveCoins($player, $amount, $target);
    }

    public function getDescription(?string $subcmd): string
    {
        return "Transfer credits to another player or NPC.";
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            "<amount> ['to'] <target>",
        ];
    }

    public function getSeeAlso(?string $subcmd): array
    {
        return ['credits'];
    }
}
