<?php
/**
 * Gauntlet MUD - Map of commands
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Commands\Admin\Ban;
use Gauntlet\Commands\Admin\Connections;
use Gauntlet\Commands\Admin\Debug;
use Gauntlet\Commands\Admin\Disconnect;
use Gauntlet\Commands\Admin\Drain;
use Gauntlet\Commands\Admin\EchoCmd;
use Gauntlet\Commands\Admin\EvalLisp;
use Gauntlet\Commands\Admin\GotoRoom;
use Gauntlet\Commands\Admin\Listing;
use Gauntlet\Commands\Admin\Load;
use Gauntlet\Commands\Admin\Peace;
use Gauntlet\Commands\Admin\Probe;
use Gauntlet\Commands\Admin\Purge;
use Gauntlet\Commands\Admin\Reload;
use Gauntlet\Commands\Admin\Reset;
use Gauntlet\Commands\Admin\Restore;
use Gauntlet\Commands\Admin\Server;
use Gauntlet\Commands\Admin\Shutdown;
use Gauntlet\Commands\Admin\Todo;
use Gauntlet\Commands\Comm\BulletinBoard;
use Gauntlet\Commands\Comm\Comm;
use Gauntlet\Commands\Comm\Emote;
use Gauntlet\Commands\Comm\Mail;
use Gauntlet\Commands\Comm\Say;
use Gauntlet\Commands\Comm\Tell;
use Gauntlet\Commands\Fight\Assist;
use Gauntlet\Commands\Fight\Attack;
use Gauntlet\Commands\Fight\Flee;
use Gauntlet\Commands\Fight\Rescue;
use Gauntlet\Commands\Info\Commands;
use Gauntlet\Commands\Info\Consider;
use Gauntlet\Commands\Info\Date;
use Gauntlet\Commands\Info\Dict;
use Gauntlet\Commands\Info\Equipment;
use Gauntlet\Commands\Info\Exits;
use Gauntlet\Commands\Info\Find;
use Gauntlet\Commands\Info\Help;
use Gauntlet\Commands\Info\Info;
use Gauntlet\Commands\Info\Inventory;
use Gauntlet\Commands\Info\Look;
use Gauntlet\Commands\Info\Sense;
use Gauntlet\Commands\Info\Stats;
use Gauntlet\Commands\Info\Who;
use Gauntlet\Commands\Info\Whoami;
use Gauntlet\Commands\Interface\Alias;
use Gauntlet\Commands\Interface\ColorPref;
use Gauntlet\Commands\Interface\Preferences;
use Gauntlet\Commands\Interface\Repeat;
use Gauntlet\Commands\Items\Drop;
use Gauntlet\Commands\Items\Get;
use Gauntlet\Commands\Items\Give;
use Gauntlet\Commands\Items\Put;
use Gauntlet\Commands\Items\Remove;
use Gauntlet\Commands\Items\Wear;
use Gauntlet\Commands\Bank;
use Gauntlet\Commands\DoorCmd;
use Gauntlet\Commands\GroupCmd;
use Gauntlet\Commands\Move;
use Gauntlet\Commands\Quit;
use Gauntlet\Commands\Save;
use Gauntlet\Commands\ShopCmd;
use Gauntlet\Commands\Title;
use Gauntlet\Commands\Train;
use Gauntlet\Commands\Transfer;

use Gauntlet\Enum\AdminLevel;
use Gauntlet\Enum\Direction;
use Gauntlet\Enum\MoneyType;
use Gauntlet\Util\Config;

class CommandMap
{
    private static ?array $map = null;

    private static function getMap(): array
    {
        if (!self::$map) {
            self::$map = [
                new CommandInfo('!', 'Repeat'),

                // Movement
                new CommandInfo('north', Move::class, Direction::North->value),
                new CommandInfo('east', Move::class, Direction::East->value),
                new CommandInfo('south', Move::class, Direction::South->value),
                new CommandInfo('west', Move::class, Direction::West->value),
                new CommandInfo('up', Move::class, Direction::Up->value),
                new CommandInfo('down', Move::class, Direction::Down->value),

                new CommandInfo('alias', Alias::class, Alias::ALIAS),
                new CommandInfo('assist', Assist::class),
                new CommandInfo('balance', Bank::class, Bank::BALANCE),
                new CommandInfo('board', BulletinBoard::class),
                new CommandInfo('buy', ShopCmd::class, ShopCmd::BUY),
                new CommandInfo('close', DoorCmd::class, DoorCmd::CLOSE),
                (Config::moneyType() == MoneyType::Coins) ? (new CommandInfo('coin', Stats::class, Stats::COIN)) : null,
                new CommandInfo('colorpref', ColorPref::class),
                new CommandInfo('commands', Commands::class, Commands::COMMANDS),
                new CommandInfo('consider', Consider::class),
                (Config::moneyType() == MoneyType::Credits) ? (new CommandInfo('credits', Stats::class, Stats::COIN)) : null,
                new CommandInfo('date', Date::class),
                new CommandInfo('deposit', Bank::class, Bank::DEPOSIT),
                new CommandInfo('dictionary', Dict::class, Dict::DICTIONARY),
                new CommandInfo('discard', Drop::class, Drop::DISCARD),
                new CommandInfo('drop', Drop::class, Drop::DROP),
                new CommandInfo('equipment', Equipment::class),
                new CommandInfo('emote', Emote::class),
                new CommandInfo('exits', Exits::class),
                new CommandInfo('flee', Flee::class),
                new CommandInfo('find', Find::class),
                new CommandInfo('get', Get::class),
                new CommandInfo('give', Give::class),
                new CommandInfo('gossip', Comm::class, Comm::GOSSIP),
                new CommandInfo('hit', Attack::class),
                new CommandInfo('help', Help::class),
                new CommandInfo('inventory', Inventory::class),
                new CommandInfo('info', Info::class),
                new CommandInfo('kill', Attack::class),
                new CommandInfo('look', Look::class),
                new CommandInfo('lock', DoorCmd::class, DoorCmd::LOCK),
                new CommandInfo('list', ShopCmd::class, ShopCmd::LIST),
                new CommandInfo('mail', Mail::class),
                new CommandInfo('open', DoorCmd::class, DoorCmd::OPEN),
                new CommandInfo('ooc', Comm::class, Comm::OOC),
                new CommandInfo('party', GroupCmd::class),
                new CommandInfo('put', Put::class),
                new CommandInfo('preferences', Preferences::class),
                new CommandInfo('quit', Quit::class),
                new CommandInfo('remove', Remove::class),
                new CommandInfo('rescue', Rescue::class),
                new CommandInfo('say', Say::class),
                new CommandInfo('save', Save::class),
                new CommandInfo('sense', Sense::class),
                new CommandInfo('sell', ShopCmd::class, ShopCmd::SELL),
                new CommandInfo('socials', Commands::class, Commands::SOCIALS),
                new CommandInfo('stats', Stats::class, Stats::STATS),
                new CommandInfo('tell', Tell::class),
                new CommandInfo('time', Date::class),
                new CommandInfo('title', Title::class),
                new CommandInfo('train', Train::class),
                (Config::moneyType() == MoneyType::Credits) ? (new CommandInfo('transfer', Transfer::class)) : null,
                // new CommandInfo('thesaurus', Dict::class, Dict::THESAURUS),
                new CommandInfo('unlock', DoorCmd::class, DoorCmd::UNLOCK),
                new CommandInfo('unalias', Alias::class, Alias::UNALIAS),
                new CommandInfo('wear', Wear::class, Wear::WEAR),
                new CommandInfo('who', Who::class),
                new CommandInfo('whoami', Whoami::class),
                new CommandInfo('wield', Wear::class, Wear::WIELD),
                new CommandInfo('withdraw', Bank::class, Bank::WITHDRAW),

                // Admin commands
                new CommandInfo('admin', Comm::class, Comm::ADMIN, AdminLevel::Immortal),
                new CommandInfo('ban', Ban::class, null, AdminLevel::GreaterGod),
                new CommandInfo('conns', Connections::class, null, AdminLevel::GreaterGod),
                new CommandInfo('debug', Debug::class, null, AdminLevel::Implementor),
                new CommandInfo('discon', Disconnect::class, null, AdminLevel::GreaterGod),
                new CommandInfo('drain', Drain::class, null, AdminLevel::DemiGod),
                new CommandInfo('echor', EchoCmd::class, EchoCmd::ROOM, AdminLevel::DemiGod),
                new CommandInfo('echoz', EchoCmd::class, EchoCmd::ZONE, AdminLevel::DemiGod),
                new CommandInfo('echog', EchoCmd::class, EchoCmd::GLOBAL, AdminLevel::God),
                new CommandInfo('eval', EvalLisp::class, EvalLisp::EVAL, AdminLevel::God),
                new CommandInfo('evalas', EvalLisp::class, EvalLisp::EVALAS, AdminLevel::God),
                new CommandInfo('goto', GotoRoom::class, null, AdminLevel::Immortal),
                new CommandInfo('load', Load::class, null, AdminLevel::God),
                new CommandInfo('peace', Peace::class, null, AdminLevel::DemiGod),
                new CommandInfo('probe', Probe::class, null, AdminLevel::DemiGod),
                new CommandInfo('purge', Purge::class, null, AdminLevel::DemiGod),
                new CommandInfo('query', Listing::class, null, AdminLevel::DemiGod),
                new CommandInfo('reload', Reload::class, null, AdminLevel::GreaterGod),
                new CommandInfo('restore', Restore::class, null, AdminLevel::DemiGod),
                new CommandInfo('reset', Reset::class, null, AdminLevel::God),
                new CommandInfo('server', Server::class, null, AdminLevel::Immortal),
                new CommandInfo('shutdown', Shutdown::class, null, AdminLevel::Implementor),
                new CommandInfo('todo', Todo::class, null, AdminLevel::DemiGod),
            ];
        }

        return self::$map;
    }

    public function getCommand(string $input, ?AdminLevel $admin): ?CommandInfo
    {
        foreach (self::getMap() as $cmd) {
            if (!$cmd) {
                continue;
            }
            if (!str_starts_with_case($cmd->getAlias(), $input)) {
                continue;
            }
            if (!AdminLevel::validate($cmd->getAdmin(), $admin)) {
                continue;
            }

            return $cmd;
        }

        return null;
    }

    public function getList(?AdminLevel $admin, bool $onlyAdmin = false): array
    {
        $list = [];

        foreach (self::getMap() as $cmd) {
            if (!$cmd) {
                continue;
            }
            if (!AdminLevel::validate($cmd->getAdmin(), $admin)) {
                continue;
            }
            if (!$cmd->getAdmin() && $onlyAdmin) {
                continue;
            }

            $list[] = $cmd->getAlias();
        }

        return $list;
    }
}
