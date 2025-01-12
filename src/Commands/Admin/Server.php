<?php
/**
 * Gauntlet MUD - Server command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Admin;

use Gauntlet\CommandMap;
use Gauntlet\Lists;
use Gauntlet\MainLoop;
use Gauntlet\Player;
use Gauntlet\Socials;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Enum\AdminLevel;
use Gauntlet\Util\Input;
use Gauntlet\Util\Sleeper;
use Gauntlet\Util\TimeFormatter;

class Server extends BaseCommand
{
    public function __construct(
        protected MainLoop $mainLoop,
        protected Lists $lists,
        protected Sleeper $sleeper,
        protected CommandMap $commands,
        protected Socials $socials
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        $version = getenv('GAUNTLET_VERSION');
        if ($version) {
            $player->outln('Version: ' . $version);
        }

        $uptime = time() - $this->mainLoop->getStartTime();
        $memUsed = memory_get_usage() / (1024 * 1024);
        $memTotal = memory_get_usage(true) / (1024 * 1024);

        $gc = gc_status();

        $shopItemCount = 0;
        $playerCount = 0;
        $monsterCount = 0;

        foreach ($this->lists->getShops()->getAll() as $shop) {
            $shopItemCount += count($shop->getItemIds());
        }

        foreach ($this->lists->getLiving()->getAll() as $living) {
            if ($living->isMonster()) {
                $monsterCount++;
            } else {
                $playerCount++;
            }
        }

        $player->outln('PHP: %s', phpversion());
        $player->outln('Host: %s %s %s', php_uname('s'), php_uname('r'), php_uname('m'));
        $player->outln('Uptime: %s', TimeFormatter::timeToString($uptime));
        $player->outln('Memory: %.2f / %.2f MB', $memUsed, $memTotal);
        $player->outln('CPU: %.2f %%', $this->sleeper->getWorkload() * 100);
        $player->outln('GC: %d / %d refs, %d freed in %d runs', $gc['roots'], $gc['threshold'], $gc['collected'], $gc['runs']);
        $player->outln();
        $player->outln('%d commands and %d socials.', count($this->commands->getList(AdminLevel::Implementor)), count($this->socials->list()));
        $player->outln('%d zones from %d templates.', $this->lists->getZones()->count(), $this->lists->getZoneTemplates()->count());
        $player->outln('%d rooms from %d templates.', $this->lists->getRooms()->count(), $this->lists->getRoomTemplates()->count());
        $player->outln('%d shops selling %d items.', $this->lists->getShops()->count(), $shopItemCount);
        $player->outln('%d items from %d templates.', $this->lists->getItems()->count(), $this->lists->getItemTemplates()->count());
        $player->outln('%d monsters from %d templates.', $monsterCount, $this->lists->getMonsterTemplates()->count());
        $player->outln('%d connections and %d players.', $this->lists->getDescriptors()->count(),  $playerCount);
    }

    public function getDescription(?string $subcmd): string
    {
        return 'Display information and statistics about the game state.';
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            '',
        ];
    }
}
