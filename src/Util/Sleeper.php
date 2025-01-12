<?php
/**
 * Gauntlet MUD - Helper class for sleeping
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Util;

class Sleeper
{
    protected int $resetTime;
    protected int $interval;

    protected array $work = [];

    public function __construct()
    {
        $this->interval = TIME_TICK;
    }

    public function getWorkload(): float
    {
        return array_sum($this->work) / count($this->work);
    }

    public function reset(): void
    {
        $this->resetTime = $this->currentTimeMs();
    }

    public function sleep(int $i): void
    {
        $elapsedTime = $this->currentTimeMs() - $this->resetTime;
        $sleepTime = $this->interval - $elapsedTime;

        if ($sleepTime > 0) {
            usleep($sleepTime * 1000);
        }

        $this->work[$i % 100] = $elapsedTime / $this->interval;
    }

    private function currentTimeMs(): int
    {
        return (int) round(microtime(true) * 1000);
    }
}
