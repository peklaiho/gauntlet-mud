<?php
/**
 * Gauntlet MUD - Directions
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Enum;

enum Direction: string
{
    case North = 'N';
    case East = 'E';
    case South = 'S';
    case West = 'W';
    case Up = 'U';
    case Down = 'D';

    public static function parseFromName(string $name): ?Direction
    {
        return match($name) {
            'north' => Direction::North,
            'east' => Direction::East,
            'south' => Direction::South,
            'west' => Direction::West,
            'up' => Direction::Up,
            'down' => Direction::Down,
            default => null
        };
    }

    public function name(): string
    {
        return match($this) {
            Direction::North => 'north',
            Direction::East => 'east',
            Direction::South => 'south',
            Direction::West => 'west',
            Direction::Up => 'up',
            Direction::Down => 'down'
        };
    }

    public function opposite(): Direction
    {
        return match($this) {
            Direction::North => Direction::South,
            Direction::East => Direction::West,
            Direction::South => Direction::North,
            Direction::West => Direction::East,
            Direction::Up => Direction::Down,
            Direction::Down => Direction::Up
        };
    }

    public function oppositeName(): string
    {
        return match($this) {
            Direction::North => 'south',
            Direction::East => 'west',
            Direction::South => 'north',
            Direction::West => 'east',
            Direction::Up => 'below',
            Direction::Down => 'above'
        };
    }
}
