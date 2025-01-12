<?php
/**
 * Gauntlet MUD - Echo command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Admin;

use Gauntlet\Lists;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Input;

class EchoCmd extends BaseCommand
{
    public const ROOM = 'room';
    public const ZONE = 'zone';
    public const GLOBAL = 'global';

    public function __construct(
        protected Lists $lists
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($input->empty()) {
            $player->outln('What do you wish to echo?');
            return;
        }

        $message = ucfirst($input->getWholeArgument(true));

        // End the sentence with a dot if it is not present
        $last = substr($message, strlen($message) - 1);
        if (!in_array($last, ['.', '!', '?'])) {
            $message .= '.';
        }

        foreach ($this->lists->getLiving()->getAll() as $living) {
            if (!$living->isPlayer()) {
                continue;
            }
            if ($subcmd == self::ROOM && $living->getRoom() !== $player->getRoom()) {
                continue;
            }
            if ($subcmd == self::ZONE && $living->getRoom()->getZone() !== $player->getRoom()->getZone()) {
                continue;
            }

            $living->outln($message);
        }
    }

    public function getDescription(?string $subcmd): string
    {
        $msg = match ($subcmd) {
            self::ROOM => 'same room as yourself',
            self::ZONE => 'same zone as yourself',
            default => 'game'
        };

        return "Display a message to all players in the $msg. The message is capitalized and a dot is appended if the input does not end with a punctuation mark.";
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            '<message>'
        ];
    }
}
