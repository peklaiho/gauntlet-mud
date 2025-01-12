<?php
/**
 * Gauntlet MUD - Trait for keywords
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Trait;

trait Keywords
{
    protected array $keywords = [];

    public function getKeywords(): array
    {
        return $this->keywords;
    }

    public function hasKeyword(string $search): bool
    {
        foreach ($this->keywords as $kw) {
            if (str_starts_with_case($kw, $search)) {
                return true;
            }
        }

        return false;
    }

    public function setKeywords(array $val): void
    {
        $this->keywords = $val;
    }
}
