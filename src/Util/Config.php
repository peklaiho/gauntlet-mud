<?php
/**
 * Gauntlet MUD - Configuration
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Util;

use Gauntlet\Enum\EqSlot;
use Gauntlet\Enum\GameType;
use Gauntlet\Enum\MoneyType;

class Config
{
    public static function gameType(): GameType
    {
        return GameType::Fantasy;
    }

    public static function moneyType(): MoneyType
    {
        if (self::gameType() == GameType::SciFi) {
            return MoneyType::Credits;
        }

        return MoneyType::Coins;
    }

    public static function gameName(): string
    {
        return 'Mistport';
    }

    public static function startingEquipment(): array
    {
        // Item number, equipment slot
        // Load to inventory if eqslot is null

        return [
            [101, EqSlot::Wield], // knife
            [205, EqSlot::Feet], // sandals
            [231, EqSlot::Chest], // robe
        ];
    }

    public static function startingRoomId(): int
    {
        return 1000;
    }

    public static function startingZoneId(): int
    {
        return 10;
    }

    public static function useSkillPoints(): bool
    {
        return false;
    }
}
