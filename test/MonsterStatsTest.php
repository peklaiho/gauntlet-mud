<?php
/**
 * Gauntlet MUD - Unit tests for MonsterStats
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MonsterStatsTest extends TestCase
{
    public static function damageMultipleProvider(): array
    {
        return [
            [1, 1.0],
            [13, 2.0],
            [25, 3.0],
            [37, 4.0],
            [49, 5.0],
        ];
    }

    #[DataProvider('damageMultipleProvider')]
    public function testGetDamageMultiplier(int $monsterLevel, float $expected)
    {
        $result = Gauntlet\MonsterStats::getDamageMultiplier($monsterLevel);

        $this->assertSame($expected, $result);
    }

    public static function expMultiplierProvider(): array
    {
        return [
            [1, 1.000],
            [20, 1.388],
            [30, 1.592],
            [40, 1.796],
            [50, 2.000],
        ];
    }

    #[DataProvider('expMultiplierProvider')]
    public function testGetExpMultiplier(int $monsterLevel, float $expected)
    {
        $result = Gauntlet\MonsterStats::getExpMultiplier($monsterLevel);

        $result = round($result, 3);

        $this->assertSame($expected, $result);
    }
}
