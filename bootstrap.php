<?php
/**
 * Gauntlet MUD - Constants, global utility functions and service container
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

require(__DIR__ . '/vendor/autoload.php');

// Constants
define('TIME_TICK', 100); // ms

// Update intervals in ticks
define('UPDATE_LIVING', 50);
define('UPDATE_ITEMS',  50);
define('UPDATE_FIGHTS', 25);
define('UPDATE_ZONES', 100);
define('UPDATE_ROOMS',  50);
define('UPDATE_TIME',   10);

// Time until corpses decay, seconds
define('MONSTER_CORPSE_DECAY', 15 * 60);
define('PLAYER_CORPSE_DECAY', 120 * 60);

// Chance to hit
define('BASE_TO_HIT', 75);
define('MAX_TO_HIT', 95);
define('MIN_TO_HIT', 5);

define('MAX_LEVEL', 50);
define('BASE_ATTR', 10);

// Common messages
define('MESSAGE_DARK', 'It is pitch black...');
define('MESSAGE_NOONE', 'No-one here by that name.');
define('MESSAGE_NOTHING', 'Nothing here by that name.');

// Global functions

function ordinal_indicator(int $val): string
{
    if ($val == 11 || $val == 12 || $val == 13) {
        return $val . 'th';
    }

    if (str_ends_with($val, 1)) {
        return $val . 'st';
    } elseif (str_ends_with($val, 2)) {
        return $val . 'nd';
    } elseif (str_ends_with($val, 3)) {
        return $val . 'rd';
    }

    return $val . 'th';
}

/**
 * Case-insensitive version of str_contains.
 */
function str_contains_case(string $str, string $search): bool
{
    return str_contains(strtolower($str), strtolower($search));
}

/**
 * Case-insensitive version of str_starts_with.
 */
function str_starts_with_case(string $str, string $start): bool
{
    return str_starts_with(strtolower($str), strtolower($start));
}

/**
 * Case-insensitive version of str_ends_with.
 */
function str_ends_with_case(string $str, string $end): bool
{
    return str_ends_with(strtolower($str), strtolower($end));
}

// Service container

function get_di_container(): Gauntlet\Util\ServiceContainer
{
    $container = new Gauntlet\Util\ServiceContainer();

    $container->set(Gauntlet\Network\INetwork::class, function ($c, int $depth) {
        return new Gauntlet\Network\TcpServer();
    });

    // Repositories

    $container->set(Gauntlet\Data\IBulletinBoardRepository::class, function ($c, int $depth) {
        return new Gauntlet\Data\YamlBulletinBoardRepository();
    });

    $container->set(Gauntlet\Data\IFactionRepository::class, function ($c, int $depth) {
        return new Gauntlet\Data\YamlFactionRepository();
    });

    $container->set(Gauntlet\Data\IItemTemplateRepository::class, function ($c, int $depth) {
        return new Gauntlet\Data\YamlItemTemplateRepository();
    });

    $container->set(Gauntlet\Data\IMailRepository::class, function ($c, int $depth) {
        return new Gauntlet\Data\YamlMailRepository();
    });

    $container->set(Gauntlet\Data\IMonsterTemplateRepository::class, function ($c, int $depth) {
        return new Gauntlet\Data\YamlMonsterTemplateRepository();
    });

    $container->set(Gauntlet\Data\IPlayerRepository::class, function ($c, int $depth) {
        $playerItems = $c->get(Gauntlet\PlayerItems::class, $depth);
        return new Gauntlet\Data\YamlPlayerRepository($playerItems);
    });

    $container->set(Gauntlet\Data\IRoomRepository::class, function ($c, int $depth) {
        return new Gauntlet\Data\YamlRoomRepository();
    });

    $container->set(Gauntlet\Data\IShopRepository::class, function ($c, int $depth) {
        return new Gauntlet\Data\YamlShopRepository();
    });

    $container->set(Gauntlet\Data\IZoneRepository::class, function ($c, int $depth) {
        return new Gauntlet\Data\YamlZoneRepository();
    });

    return $container;
}

define('SERVICE_CONTAINER', get_di_container());
