<?php
/**
 * Gauntlet MUD - Map of spells
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Enum\Modifier;
use Gauntlet\Enum\Spell;
use Gauntlet\Spell\AffectionSpell;
use Gauntlet\Spell\BaseSpell;
use Gauntlet\Spell\DamageSpell;

class SpellMap
{
    private static array $map = [];

    public static function get(Spell $spell): BaseSpell
    {
        if (!self::$map) {
            // Cleric
            self::$map[Spell::MinorProtection->value] = new AffectionSpell(Spell::MinorProtection, 10, [
                Modifier::Armor->value => 1.5
            ], 600, 'You feel slightly more protected.', 'You no longer feel protected.');

            self::$map[Spell::MajorProtection->value] = new AffectionSpell(Spell::MajorProtection, 50, [
                Modifier::Armor->value => 4
            ], 600, 'You feel significantly more protected.', 'You no longer feel protected.');

            // Mage damage spells
            self::$map[Spell::MagicMissile->value] = new DamageSpell(Spell::MagicMissile, 10, 10, 2);
            self::$map[Spell::FireBolt->value] = new DamageSpell(Spell::FireBolt, 10, 10, 2);
            self::$map[Spell::ChillBones->value] = new DamageSpell(Spell::ChillBones, 10, 10, 2);
            self::$map[Spell::FireBall->value] = new DamageSpell(Spell::FireBall, 10, 10, 2);
            self::$map[Spell::AlphaAndOmega->value] = new DamageSpell(Spell::AlphaAndOmega, 200, 300, 10);
        }

        return self::$map[$spell->value];
    }
}
