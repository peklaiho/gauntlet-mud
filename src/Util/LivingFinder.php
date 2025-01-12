<?php
/**
 * Gauntlet MUD - Find living entities from lists
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Util;

use Gauntlet\Living;

class LivingFinder
{
    protected bool $excMonsters = false;
    protected bool $excPlayers = false;
    protected bool $excSelf = false;
    protected ?Living $excLiving = null;

    public function __construct(
        protected Living $searcher,
        protected array $lists
    ) {

    }

    public function excludeMonsters(bool $val = true): self
    {
        $this->excMonsters = $val;
        return $this;
    }

    public function excludePlayers(bool $val = true): self
    {
        $this->excPlayers = $val;
        return $this;
    }

    public function excludeSelf(bool $val = true): self
    {
        $this->excSelf = $val;
        return $this;
    }

    public function excludeLiving(?Living $val): self
    {
        $this->excLiving = $val;
        return $this;
    }

    public function find(string $txt): ?Living
    {
        $skip = 0;
        if (strpos($txt, '.') !== false) {
            $parts = explode('.', $txt);
            $skip = intval($parts[0]) - 1;
            $txt = $parts[1];
        }

        foreach ($this->lists as $list) {
            foreach ($list->getAll() as $living) {
                if (!$this->searcher->canSee($living)) {
                    continue;
                }
                if ($this->excSelf && $living === $this->searcher) {
                    continue;
                }
                if ($living === $this->excLiving) {
                    continue;
                }

                if ($living->isMonster()) {
                    if ($this->excMonsters) {
                        continue;
                    }
                    if (!$living->getTemplate()->hasKeyword($txt)) {
                        continue;
                    }
                } else {
                    if ($this->excPlayers) {
                        continue;
                    }
                    if (!str_starts_with_case($living->getname(), $txt)) {
                        continue;
                    }
                }

                if ($skip > 0) {
                    $skip--;
                } else {
                    return $living;
                }
            }
        }

        return null;
    }
}
