<?php
/**
 * Gauntlet MUD - Find items from lists
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Util;

use Gauntlet\Item;
use Gauntlet\Living;

class ItemFinder
{
    public function find(Living $searcher, string $txt, array $lists, ?callable $filter = null): ?Item
    {
        $skip = 0;
        if (strpos($txt, '.') !== false) {
            $parts = explode('.', $txt);
            $skip = intval($parts[0]) - 1;
            $txt = $parts[1];
        }

        foreach ($lists as $list) {
            foreach ($list->getAll() as $item) {
                if (!$searcher->canSeeItem($item)) {
                    continue;
                }

                if ($filter && !$filter($item)) {
                    continue;
                }

                if ($item->getTemplate()->hasKeyword($txt)) {
                    if ($skip > 0) {
                        $skip--;
                    } else {
                        return $item;
                    }
                }
            }
        }

        return null;
    }
}
