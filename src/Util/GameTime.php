<?php
/**
 * Gauntlet MUD - Helper functions for date and time
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Util;

use Gauntlet\Enum\PartOfDay;

class GameTime
{
    const TIME_EPOCH = 1640995200; // unix timestamp of epoch, 2022-01-01 00:00:00 UTC
    const TIME_RATIO = 4; // 1 second in real time = 4 seconds in game time
    const YEAR_OFFSET = 520; // starting year of game time

    const MONTHS_IN_YEAR = 4;
    const DAYS_IN_MONTH = 90;
    const HOURS_IN_DAY = 24;
    const MINUTES_IN_HOUR = 60;
    const SECONDS_IN_MINUTE = 60;

    const SUNRISE = 6; // 6:00
    const EVENING = 18; // 18:00
    const SUNSET= 20; // 20:00
    const TWILIGHT_DURATION = 30; // minutes

    // Get current game time
    public static function now(): self
    {
        return self::fromUnixTime(time());
    }

    public static function fromUnixTime(int $unixTime): self
    {
        return new static(($unixTime - self::TIME_EPOCH) * self::TIME_RATIO);
    }

    protected array $parts;

    public function __construct(
        protected int $time
    ) {
        $secondsInHour = self::MINUTES_IN_HOUR * self::SECONDS_IN_MINUTE;
        $secondsInDay = self::HOURS_IN_DAY * $secondsInHour;
        $secondsInMonth = self::DAYS_IN_MONTH * $secondsInDay;
        $secondsInYear = self::MONTHS_IN_YEAR * $secondsInMonth;

        $year = intdiv($time, $secondsInYear);
        $remain = $time % $secondsInYear;

        $month = intdiv($remain, $secondsInMonth);
        $remain = $remain % $secondsInMonth;

        $day = intdiv($remain, $secondsInDay);
        $remain = $remain % $secondsInDay;

        $hour = intdiv($remain, $secondsInHour);
        $remain = $remain % $secondsInHour;

        $minute = intdiv($remain, self::SECONDS_IN_MINUTE);
        $second = $remain % self::SECONDS_IN_MINUTE;

        $this->parts = [
            $year,
            $month,
            $day,
            $hour,
            $minute,
            $second
        ];
    }

    public function time(): int
    {
        return $this->time;
    }

    public function unixTime(): int
    {
        return intdiv($this->time, self::TIME_RATIO) + self::TIME_EPOCH;
    }

    public function year(): int
    {
        return $this->parts[0] + self::YEAR_OFFSET;
    }

    public function month(): int
    {
        return $this->parts[1] + 1;
    }

    public function day(): int
    {
        return $this->parts[2] + 1;
    }

    public function hour(): int
    {
        return $this->parts[3];
    }

    public function minute(): int
    {
        return $this->parts[4];
    }

    public function second(): int
    {
        return $this->parts[5];
    }

    public function raw(): array
    {
        return $this->parts;
    }

    public function monthName(): string
    {
        static $months = [
            'Spring',
            'Summer',
            'Autumn',
            'Winter'
        ];

        return $months[$this->parts[1]];
    }

    // Time using 12h clock
    public function time12h(): string
    {
        $hour = $this->hour();
        $min = $this->minute();

        if ($hour >= 12) {
            $suffix = 'PM';

            if ($hour >= 13) {
                $hour -= 12;
            }
        } else {
            $suffix = 'AM';

            if ($hour == 0) {
                $hour = 12;
            }
        }

        return sprintf('%d:%02d %s', $hour, $min, $suffix);
    }

    public function getPartOfDay(): PartOfDay
    {
        $hour = $this->hour();
        $min = $this->minute();

        if ($hour == self::SUNRISE - 1 && $min >= self::TWILIGHT_DURATION) {
            return PartOfDay::Dawn;
        } elseif ($hour == self::SUNSET && $min < self::TWILIGHT_DURATION) {
            return PartOfDay::Dusk;
        } elseif ($hour >= self::SUNRISE && $hour < 12) {
            return PartOfDay::Morning;
        } elseif ($hour >= 12 && $hour < self::EVENING) {
            return PartOfDay::Afternoon;
        } elseif ($hour >= self::EVENING && $hour < self::SUNSET) {
            return PartOfDay::Evening;
        }

        return PartOfDay::Night;
    }
}
