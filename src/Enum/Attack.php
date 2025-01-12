<?php
/**
 * Gauntlet MUD - Attack types
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Enum;

enum Attack: string
{
    case Bite = 'bite';
    case Bludgeon = 'bludgeon';
    case Chop = 'chop';
    case Claw = 'claw';
    case Cleave = 'cleave';
    case Gore = 'gore';
    case Hammer = 'hammer';
    case Hit = 'hit';
    case Kick = 'kick';
    case Maul = 'maul';
    case Peck = 'peck';
    case Pierce = 'pierce';
    case Pound = 'pound';
    case Pummel = 'pummel';
    case Punch = 'punch';
    case Rend = 'rend';
    case Shoot = 'shoot';
    case Skewer = 'skewer';
    case Slap = 'slap';
    case Slash = 'slash';
    case Smite = 'smite';
    case Stab = 'stab';
    case Sting = 'sting';
    case Stomp = 'stomp';
    case Swing = 'swing';
    case Thrash = 'thrash';
    case Thrust = 'thrust';
    case Trample = 'trample';
    case Whack = 'whack';
    case Whip = 'whip';
    case Zap = 'zap';

    public function fightMessage(): array
    {
        return match($this) {
            Attack::Bite =>     [ 'bite',       'bite',       'bites' ],
            Attack::Bludgeon => [ 'budgeon',    'bludgeon',   'bludgeons' ],
            Attack::Chop =>     [ 'chop',       'chop',       'chops' ],
            Attack::Claw =>     [ 'claw',       'claw',       'claws' ],
            Attack::Cleave =>   [ 'cleave',     'cleave',     'cleaves' ],
            Attack::Gore =>     [ 'gore',       'gore',       'gores' ],
            Attack::Hammer =>   [ 'hammer',     'hammer',     'hammers' ],
            Attack::Hit =>      [ 'hit',        'hit',        'hits' ],
            Attack::Kick =>     [ 'kick',       'kick',       'kicks' ],
            Attack::Maul =>     [ 'maul',       'maul',       'mauls' ],
            Attack::Peck =>     [ 'peck',       'peck',       'pecks' ],
            Attack::Pierce =>   [ 'pierce',     'pierce',     'pierces' ],
            Attack::Pound =>    [ 'pound',      'pound',      'pounds' ],
            Attack::Pummel =>   [ 'pummel',     'pummel',     'pummels' ],
            Attack::Punch =>    [ 'punch',      'punch',      'punches' ],
            Attack::Rend =>     [ 'rend',       'rend',       'rends' ],
            Attack::Shoot =>    [ 'shot',       'shoot',      'shoots' ],
            Attack::Skewer =>   [ 'skewer',     'skewer',     'skewers' ],
            Attack::Slap =>     [ 'slap',       'slap',       'slaps' ],
            Attack::Slash =>    [ 'slash',      'slash',      'slashes' ],
            Attack::Smite =>    [ 'smite',      'smite',      'smites' ],
            Attack::Stab =>     [ 'stab',       'stab',       'stabs' ],
            Attack::Sting =>    [ 'sting',      'sting',      'stings' ],
            Attack::Stomp =>    [ 'stomp',      'stomp on',   'stomps on'],
            Attack::Swing =>    [ 'swing',      'swing at',   'swings at' ],
            Attack::Thrash =>   [ 'thrash',     'thrash',     'thrashes' ],
            Attack::Thrust =>   [ 'thrust',     'thrust at',  'thrusts at' ],
            Attack::Trample =>  [ 'trample',    'trample',    'tramples'],
            Attack::Whack =>    [ 'whack',      'whack',      'whacks' ],
            Attack::Whip =>     [ 'whip',       'whip',       'whips' ],
            Attack::Zap =>      [ 'zap',        'zap',        'zaps' ],
        };
    }
}
