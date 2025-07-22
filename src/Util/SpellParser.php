<?php
/**
 * Gauntlet MUD - Parser for spell names
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Util;

class SpellParser
{
    public static function parse(Input $input, array $spells): ?array
    {
        if ($input->empty()) {
            return null;
        }

        foreach ($spells as $spell) {
            $words = explode(' ', $spell->value);

            for ($i = 0; $i < $input->count(); $i++) {
                if (count($words) <= $i || !str_starts_with_case($words[$i], $input->get($i))) {
                    if ($i > 0) {
                        return [
                            $spell,
                            $input->get($i)
                        ];
                    } else {
                        continue 2;
                    }
                }
            }

            return [$spell, null];
        }

        return null;
    }
}
