<?php
/**
 * Gauntlet MUD - Train command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands;

use Gauntlet\Act;
use Gauntlet\Player;
use Gauntlet\Enum\Attribute;
use Gauntlet\Util\Input;
use Gauntlet\Util\TableFormatter;

class Train extends BaseCommand
{
    public function __construct(
        protected Act $act
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($input->empty()) {
            $rows = TableFormatter::format([
                ['Strength', $player->getTrainedAttribute(Attribute::Strength)],
                ['Dexterity', $player->getTrainedAttribute(Attribute::Dexterity)],
                ['Intelligence', $player->getTrainedAttribute(Attribute::Intelligence)],
                ['Constitution', $player->getTrainedAttribute(Attribute::Constitution)],
            ], ['Name', 'Training'], [0]);

            foreach ($rows as $row) {
                $player->outln($row);
            }

            $player->outln();

            $remaining = $player->getRemainingTraining();
            if ($remaining == 0) {
                $player->outln('You have no training points remaining.');
            } else {
                $player->outln('You have %d training points remaining.', $remaining);
            }

            return;
        }

        if (!$player->canSeeRoom()) {
            $player->outln(MESSAGE_DARK);
            return;
        }

        $remaining = $player->getRemainingTraining();
        if ($remaining <= 0) {
            $player->outln('You have no training points remaining.');
            return;
        }

        $attrName = strtolower($input->get(0));

        if (str_starts_with('strength', $attrName)) {
            $player->trainAttribute(Attribute::Strength);
            $player->outln('You feel stronger.');
            $this->act->toRoom('@t performs some strength training.', true, $player);
        } elseif (str_starts_with('dexterity', $attrName)) {
            $player->trainAttribute(Attribute::Dexterity);
            $player->outln('You feel more dexterous.');
            $this->act->toRoom('@t performs some dexterity training.', true, $player);
        } elseif (str_starts_with('intelligence', $attrName)) {
            $player->trainAttribute(Attribute::Intelligence);
            $player->outln('You feel smarter.');
            $this->act->toRoom('@t performs some intelligence training.', true, $player);
        } elseif (str_starts_with('constitution', $attrName)) {
            $player->trainAttribute(Attribute::Constitution);
            $player->outln('You feel vigorous.');
            $this->act->toRoom('@t performs some constitution training.', true, $player);
        } else {
            $player->outln('Which attribute do you wish to train?');
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return 'Train an attribute to increase it or display current training status without argument.';
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            '',
            '<attribute>'
        ];
    }
}
