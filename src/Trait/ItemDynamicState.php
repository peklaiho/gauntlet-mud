<?php
/**
 * Gauntlet MUD - Dynamic state for items
 * Copyright (C) 2026 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Trait;

trait ItemDynamicState
{
    protected array $dynamicState = [];

    public function getDynamicState(): array
    {
        return $this->dynamicState;
    }

    public function getDynamicStateIndex(int $index, $defaultValue = null): ?int
    {
        if (array_key_exists($index, $this->dynamicState)) {
            return $this->dynamicState[$index];
        } else {
            return $defaultValue;
        }
    }

    public function setDynamicState(array $val): void
    {
        $this->dynamicState = $val;
    }

    public function setDynamicStateIndex(int $index, int $val): void
    {
        $this->dynamicState[$index] = $val;
    }

    // Light sources

    public function getLightEnabled(): bool
    {
        return boolval($this->getDynamicStateIndex(0));
    }

    public function getLightSpentFuel(): int
    {
        return $this->getDynamicStateIndex(1, 0);
    }

    public function setLightEnabled(bool $val): void
    {
        $this->setDynamicStateIndex(0, intval($val));
    }

    public function setLightSpentFuel(int $val): void
    {
        $this->setDynamicStateIndex(1, $val);
    }
}
