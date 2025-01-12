<?php
/**
 * Gauntlet MUD - Unit tests for GameTime
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Gauntlet\Util\GameTime;

class GameTimeTest extends TestCase
{
    public static function timeProvider()
    {
        $secondsInHour = GameTime::MINUTES_IN_HOUR * GameTime::SECONDS_IN_MINUTE;
        $secondsInDay = GameTime::HOURS_IN_DAY * $secondsInHour;
        $secondsInMonth = GameTime::DAYS_IN_MONTH * $secondsInDay;
        $secondsInYear = GameTime::MONTHS_IN_YEAR * $secondsInMonth;

        return [
            [0, [0, 0, 0, 0, 0, 0]],
            [1, [0, 0, 0, 0, 0, 1]],

            [GameTime::SECONDS_IN_MINUTE, [0, 0, 0, 0, 1, 0]],
            [$secondsInHour,    [0, 0, 0, 1, 0, 0]],
            [$secondsInDay,     [0, 0, 1, 0, 0, 0]],
            [$secondsInMonth,   [0, 1, 0, 0, 0, 0]],
            [$secondsInYear,    [1, 0, 0, 0, 0, 0]],

            [$secondsInYear + $secondsInMonth + $secondsInDay + $secondsInHour +
                GameTime::SECONDS_IN_MINUTE + 1, [1, 1, 1, 1, 1, 1]],
        ];
    }

    #[DataProvider('timeProvider')]
    public function test_time(int $time, array $expected)
    {
        $gt = new GameTime($time);

        $this->assertSame($expected, $gt->raw());
    }

    public function test_now()
    {
        $unixTime = time();

        $gt = GameTime::fromUnixTime($unixTime);
        $this->assertSame(($unixTime - GameTime::TIME_EPOCH) * GameTime::TIME_RATIO, $gt->time());
        $this->assertSame($unixTime, $gt->unixTime());

        // Add one second to unix time and
        // make sure we increased by TIME_RATIO
        $gt2 = GameTime::fromUnixTime($unixTime + 1);
        $this->assertSame($gt->time() + GameTime::TIME_RATIO, $gt2->time());
        $this->assertSame($unixTime + 1, $gt2->unixTime());
    }
}
