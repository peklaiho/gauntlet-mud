<?php
/**
 * Gauntlet MUD - Unit tests for Experience
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ExperienceTest extends TestCase
{
    public static function penaltyProvider(): array
    {
        return [
            [5, 5, 1],
            [1, 5, 1],

            // 1 lower
            [9, 8, 0.95],
            [50, 49, 0.95],

            // medium
            [40, 30, 0.5],
            [24, 8, 0.2],
            [30, 13, 0.15],

            // much lower
            [50, 1, 0.1],
            [50, 2, 0.1],
        ];
    }

    #[DataProvider('penaltyProvider')]
    public function testGetPenaltyMultiplier(int $playerLevel, int $monsterLevel, float $expected)
    {
        $result = Gauntlet\Experience::getPenaltyMultiplier($playerLevel, $monsterLevel);

        $this->assertEqualsWithDelta($expected, $result, 0.00001);
    }

    public static function expGainProvider(): array
    {
        return [
            [5, 1, 400, 100, 25, 80],
            [30, 20, 400, 100, 25, 50],
            [50, 10, 400, 100, 25, 10],
        ];
    }

    #[DataProvider('expGainProvider')]
    public function testGetExpGain(int $playerLevel, int $monsterLevel, int $monsterExp, float $monsterHealth, float $damage, int $expected)
    {
        $player = $this->createStub(Gauntlet\Player::class);
        $player->method('getLevel')
            ->willReturn($playerLevel);

        $monster = $this->createStub(Gauntlet\Monster::class);
        $monster->method('getLevel')
            ->willReturn($monsterLevel);
        $monster->method('getExperience')
            ->willReturn($monsterExp);
        $monster->method('getMaxHealth')
            ->willReturn($monsterHealth);

        $result = Gauntlet\Experience::getExpGain($player, $monster, $damage);

        $this->assertSame($expected, $result);
    }
}
