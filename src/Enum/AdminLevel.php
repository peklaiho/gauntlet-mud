<?php
/**
 * Gauntlet MUD - Admin levels
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Enum;

enum AdminLevel: int
{
    case Immortal = 1;
    case DemiGod = 2;
    case God = 3;
    case GreaterGod = 4;
    case Implementor = 5;

    public function name(): string
    {
        return match($this) {
            self::Immortal => 'Immortal',
            self::DemiGod => 'Demigod',
            self::God => 'God',
            self::GreaterGod => 'Greater God',
            self::Implementor => 'Implementor'
        };
    }

    public function abbrev(): string
    {
        return match($this) {
            self::Immortal => 'Imm',
            self::DemiGod => 'Dem',
            self::God => 'God',
            self::GreaterGod => 'GrG',
            self::Implementor => 'Imp'
        };
    }

    public static function validate(?AdminLevel $required, ?AdminLevel $realized): bool
    {
        if (!$required) {
            return true;
        }

        if (!$realized) {
            return false;
        }

        return $realized->value >= $required->value;
    }
}
