<?php
/**
 * Gauntlet MUD - Disconnect command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Admin;

use Gauntlet\Lists;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Input;
use Gauntlet\Util\Log;

class Disconnect extends BaseCommand
{
    public function __construct(
        protected Lists $lists
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if (strcasecmp($input->getCommand(), 'discon') != 0) {
            $player->outln("You must type the whole command to disconnect someone.");
            return;
        }

        if ($input->empty()) {
            $player->outln('Which connection do you wish to disconnect?');
            return;
        }

        if (str_contains($input->get(0), '-')) {
            $parts = explode('-', $input->get(0));
            if (count($parts) != 2) {
                $player->outln('Invalid range.');
            }
            $ids = range(intval($parts[0]), intval($parts[1]));
        } else {
            $ids = [intval($input->get(0))];
        }

        $found = false;

        foreach ($ids as $id) {
            $conn = $this->lists->getDescriptors()->get($id);

            if ($conn) {
                $found = true;

                $logMessage = $player->getName() . ' disconnected connection #' . $conn->getId();
                if ($conn->getPlayer()) {
                    $logMessage .= ' (playing as ' . $conn->getPlayer()->getName() . ')';
                }
                Log::admin($logMessage . '.');

                $conn->close();
            }
        }

        if ($found) {
            $player->outln('Ok.');
        } else {
            $player->outln('No connection by that id.');
            return;
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return 'Disconnect a network connection.';
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            "<id>",
        ];
    }
}
