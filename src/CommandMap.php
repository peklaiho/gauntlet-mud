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
use Gauntlet\Commands\Fight\Backstab;
use Gauntlet\Commands\Fight\Disarm;
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
use Gauntlet\Commands\Info\Skills;
use Gauntlet\Commands\Info\Stats;
use Gauntlet\Commands\Info\Who;
use Gauntlet\Commands\Info\Whoami;
use Gauntlet\Commands\Interface\Alias;
use Gauntlet\Commands\Interface\ColorPref;
use Gauntlet\Commands\Interface\Preferences;
use Gauntlet\Commands\Interface\Repeat;
use Gauntlet\Commands\Items\Bury;
use Gauntlet\Commands\Items\Drop;
use Gauntlet\Commands\Items\Eat;
use Gauntlet\Commands\Items\Get;
use Gauntlet\Commands\Items\Give;
use Gauntlet\Commands\Items\Put;
use Gauntlet\Commands\Items\Remove;
use Gauntlet\Commands\Items\Wear;
use Gauntlet\Commands\Bank;
use Gauntlet\Commands\Cast;
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

class CommandMap
{
    private static ?array $map = null;

    private static function getMap(): array
    {
        if (!self::$map) {
            self::$map = [
                // new CommandInfo('!', 'Repeat'),

                // Movement
                new CommandInfo('north', SERVICE_CONTAINER->get(Move::class), Direction::North->value),
                new CommandInfo('east', SERVICE_CONTAINER->get(Move::class), Direction::East->value),
                new CommandInfo('south', SERVICE_CONTAINER->get(Move::class), Direction::South->value),
                new CommandInfo('west', SERVICE_CONTAINER->get(Move::class), Direction::West->value),
                new CommandInfo('up', SERVICE_CONTAINER->get(Move::class), Direction::Up->value),
                new CommandInfo('down', SERVICE_CONTAINER->get(Move::class), Direction::Down->value),

                new CommandInfo('alias', SERVICE_CONTAINER->get(Alias::class), Alias::ALIAS),
                new CommandInfo('assist', SERVICE_CONTAINER->get(Assist::class)),
                new CommandInfo('backstab', SERVICE_CONTAINER->get(Backstab::class)),
                new CommandInfo('balance', SERVICE_CONTAINER->get(Bank::class), Bank::BALANCE),
                new CommandInfo('board', SERVICE_CONTAINER->get(BulletinBoard::class)),
                new CommandInfo('bury', SERVICE_CONTAINER->get(Bury::class)),
                new CommandInfo('buy', SERVICE_CONTAINER->get(ShopCmd::class), ShopCmd::BUY),
                new CommandInfo('cast', SERVICE_CONTAINER->get(Cast::class)),
                new CommandInfo('close', SERVICE_CONTAINER->get(DoorCmd::class), DoorCmd::CLOSE),
                new CommandInfo('coin', SERVICE_CONTAINER->get(Stats::class), Stats::COIN),
                new CommandInfo('colorpref', SERVICE_CONTAINER->get(ColorPref::class)),
                new CommandInfo('commands', SERVICE_CONTAINER->get(Commands::class), Commands::COMMANDS),
                new CommandInfo('consider', SERVICE_CONTAINER->get(Consider::class)),
                new CommandInfo('credits', SERVICE_CONTAINER->get(Stats::class), Stats::CREDITS),
                new CommandInfo('date', SERVICE_CONTAINER->get(Date::class)),
                new CommandInfo('deposit', SERVICE_CONTAINER->get(Bank::class), Bank::DEPOSIT),
                new CommandInfo('dictionary', SERVICE_CONTAINER->get(Dict::class), Dict::DICTIONARY),
                new CommandInfo('disarm', SERVICE_CONTAINER->get(Disarm::class)),
                new CommandInfo('discard', SERVICE_CONTAINER->get(Drop::class), Drop::DISCARD),
                new CommandInfo('drop', SERVICE_CONTAINER->get(Drop::class), Drop::DROP),
                new CommandInfo('equipment', SERVICE_CONTAINER->get(Equipment::class)),
                new CommandInfo('eat', SERVICE_CONTAINER->get(Eat::class)),
                new CommandInfo('emote', SERVICE_CONTAINER->get(Emote::class)),
                new CommandInfo('exits', SERVICE_CONTAINER->get(Exits::class)),
                new CommandInfo('flee', SERVICE_CONTAINER->get(Flee::class)),
                new CommandInfo('find', SERVICE_CONTAINER->get(Find::class)),
                new CommandInfo('get', SERVICE_CONTAINER->get(Get::class)),
                new CommandInfo('give', SERVICE_CONTAINER->get(Give::class)),
                new CommandInfo('gossip', SERVICE_CONTAINER->get(Comm::class), Comm::GOSSIP),
                new CommandInfo('hit', SERVICE_CONTAINER->get(Attack::class)),
                new CommandInfo('help', SERVICE_CONTAINER->get(Help::class)),
                new CommandInfo('inventory', SERVICE_CONTAINER->get(Inventory::class)),
                new CommandInfo('info', SERVICE_CONTAINER->get(Info::class)),
                new CommandInfo('kill', SERVICE_CONTAINER->get(Attack::class)),
                new CommandInfo('look', SERVICE_CONTAINER->get(Look::class)),
                new CommandInfo('lock', SERVICE_CONTAINER->get(DoorCmd::class), DoorCmd::LOCK),
                new CommandInfo('list', SERVICE_CONTAINER->get(ShopCmd::class), ShopCmd::LIST),
                new CommandInfo('mail', SERVICE_CONTAINER->get(Mail::class)),
                new CommandInfo('open', SERVICE_CONTAINER->get(DoorCmd::class), DoorCmd::OPEN),
                new CommandInfo('ooc', SERVICE_CONTAINER->get(Comm::class), Comm::OOC),
                new CommandInfo('party', SERVICE_CONTAINER->get(GroupCmd::class)),
                new CommandInfo('put', SERVICE_CONTAINER->get(Put::class)),
                new CommandInfo('preferences', SERVICE_CONTAINER->get(Preferences::class)),
                new CommandInfo('quit', SERVICE_CONTAINER->get(Quit::class)),
                new CommandInfo('remove', SERVICE_CONTAINER->get(Remove::class)),
                new CommandInfo('rescue', SERVICE_CONTAINER->get(Rescue::class)),
                new CommandInfo('say', SERVICE_CONTAINER->get(Say::class)),
                new CommandInfo('save', SERVICE_CONTAINER->get(Save::class)),
                new CommandInfo('sense', SERVICE_CONTAINER->get(Sense::class)),
                new CommandInfo('sell', SERVICE_CONTAINER->get(ShopCmd::class), ShopCmd::SELL),
                new CommandInfo('skills', SERVICE_CONTAINER->get(Skills::class), Skills::SKILLS),
                new CommandInfo('socials', SERVICE_CONTAINER->get(Commands::class), Commands::SOCIALS),
                new CommandInfo('spells', SERVICE_CONTAINER->get(Skills::class), Skills::SPELLS),
                new CommandInfo('stats', SERVICE_CONTAINER->get(Stats::class), Stats::STATS),
                new CommandInfo('tell', SERVICE_CONTAINER->get(Tell::class)),
                new CommandInfo('time', SERVICE_CONTAINER->get(Date::class)),
                new CommandInfo('title', SERVICE_CONTAINER->get(Title::class)),
                new CommandInfo('train', SERVICE_CONTAINER->get(Train::class)),
                new CommandInfo('transfer', SERVICE_CONTAINER->get(Transfer::class)),
                // new CommandInfo('thesaurus', SERVICE_CONTAINER->get(Dict::class), Dict::THESAURUS),
                new CommandInfo('unlock', SERVICE_CONTAINER->get(DoorCmd::class), DoorCmd::UNLOCK),
                new CommandInfo('unalias', SERVICE_CONTAINER->get(Alias::class), Alias::UNALIAS),
                new CommandInfo('wear', SERVICE_CONTAINER->get(Wear::class), Wear::WEAR),
                new CommandInfo('who', SERVICE_CONTAINER->get(Who::class)),
                new CommandInfo('whoami', SERVICE_CONTAINER->get(Whoami::class)),
                new CommandInfo('wield', SERVICE_CONTAINER->get(Wear::class), Wear::WIELD),
                new CommandInfo('withdraw', SERVICE_CONTAINER->get(Bank::class), Bank::WITHDRAW),

                // Admin commands
                new CommandInfo('admin', SERVICE_CONTAINER->get(Comm::class), Comm::ADMIN, AdminLevel::Immortal),
                new CommandInfo('ban', SERVICE_CONTAINER->get(Ban::class), null, AdminLevel::GreaterGod),
                new CommandInfo('conns', SERVICE_CONTAINER->get(Connections::class), null, AdminLevel::GreaterGod),
                new CommandInfo('debug', SERVICE_CONTAINER->get(Debug::class), null, AdminLevel::Implementor),
                new CommandInfo('discon', SERVICE_CONTAINER->get(Disconnect::class), null, AdminLevel::GreaterGod),
                new CommandInfo('drain', SERVICE_CONTAINER->get(Drain::class), null, AdminLevel::DemiGod),
                new CommandInfo('echor', SERVICE_CONTAINER->get(EchoCmd::class), EchoCmd::ROOM, AdminLevel::DemiGod),
                new CommandInfo('echoz', SERVICE_CONTAINER->get(EchoCmd::class), EchoCmd::ZONE, AdminLevel::DemiGod),
                new CommandInfo('echog', SERVICE_CONTAINER->get(EchoCmd::class), EchoCmd::GLOBAL, AdminLevel::God),
                new CommandInfo('eval', SERVICE_CONTAINER->get(EvalLisp::class), EvalLisp::EVAL, AdminLevel::God),
                new CommandInfo('evalas', SERVICE_CONTAINER->get(EvalLisp::class), EvalLisp::EVALAS, AdminLevel::God),
                new CommandInfo('goto', SERVICE_CONTAINER->get(GotoRoom::class), null, AdminLevel::Immortal),
                new CommandInfo('load', SERVICE_CONTAINER->get(Load::class), null, AdminLevel::God),
                new CommandInfo('peace', SERVICE_CONTAINER->get(Peace::class), null, AdminLevel::DemiGod),
                new CommandInfo('probe', SERVICE_CONTAINER->get(Probe::class), null, AdminLevel::DemiGod),
                new CommandInfo('purge', SERVICE_CONTAINER->get(Purge::class), null, AdminLevel::DemiGod),
                new CommandInfo('query', SERVICE_CONTAINER->get(Listing::class), null, AdminLevel::DemiGod),
                new CommandInfo('reload', SERVICE_CONTAINER->get(Reload::class), null, AdminLevel::GreaterGod),
                new CommandInfo('restore', SERVICE_CONTAINER->get(Restore::class), null, AdminLevel::DemiGod),
                new CommandInfo('reset', SERVICE_CONTAINER->get(Reset::class), null, AdminLevel::God),
                new CommandInfo('server', SERVICE_CONTAINER->get(Server::class), null, AdminLevel::Immortal),
                new CommandInfo('shutdown', SERVICE_CONTAINER->get(Shutdown::class), null, AdminLevel::Implementor),
                new CommandInfo('todo', SERVICE_CONTAINER->get(Todo::class), null, AdminLevel::DemiGod),
            ];
        }

        return self::$map;
    }

    public function getCommand(Player $player, string $input): ?CommandInfo
    {
        foreach (self::getMap() as $cmd) {
            if (!str_starts_with_case($cmd->getAlias(), $input)) {
                continue;
            }
            if (!AdminLevel::validate($cmd->getAdmin(), $player->getAdminLevel())) {
                continue;
            }
            if (!$cmd->getCommand()->canExecute($player, $cmd->getSubcmd())) {
                continue;
            }

            return $cmd;
        }

        return null;
    }

    public function getList(Player $player, bool $onlyAdmin = false): array
    {
        $list = [];

        foreach (self::getMap() as $cmd) {
            if (!AdminLevel::validate($cmd->getAdmin(), $player->getAdminLevel())) {
                continue;
            }
            if (!$cmd->getAdmin() && $onlyAdmin) {
                continue;
            }
            if (!$cmd->getCommand()->canExecute($player, $cmd->getSubcmd())) {
                continue;
            }

            $list[] = $cmd->getAlias();
        }

        return $list;
    }
}
