<?php
/**
 * Gauntlet MUD - Connections command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Admin;

use Gauntlet\Lists;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\Bytes;
use Gauntlet\Util\Input;
use Gauntlet\Util\TableFormatter;
use Gauntlet\Util\TimeFormatter;

class Connections extends BaseCommand
{
    public function __construct(
        protected Lists $lists
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        $rows = [];

        foreach ($this->lists->getDescriptors()->getAll() as $desc) {
            $rows[] = [
                $desc->getId(),
                $desc->getPlayer() ? $desc->getPlayer()->getName() : '',
                TimeFormatter::timeToShortString($desc->getTime()),
                $desc->getAddress(),
                Bytes::bytesToString($desc->getReadBytes()),
                Bytes::bytesToString($desc->getWriteBytes())
            ];
        }

        $rows = TableFormatter::format($rows, ['#', 'Player', 'Time', 'IP', 'Read', 'Write'], [1]);

        foreach ($rows as $row) {
            $player->outln($row);
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return 'List network connections.';
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            "",
        ];
    }
}
