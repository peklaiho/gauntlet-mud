<?php
/**
 * Gauntlet MUD - Equipment slots
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Enum;

enum EqSlot: string
{
    case Light     = 'light';
    case Head      = 'head';
    case Neck1     = 'neck1';
    case Neck2     = 'neck2';
    case Back      = 'back';
    case Chest     = 'chest';
    case Shoulders = 'shoulders';
    case Arms      = 'arms';
    case Wrist1    = 'wrist1';
    case Wrist2    = 'wrist2';
    case Hands     = 'hands';
    case Ring1     = 'ring1';
    case Ring2     = 'ring2';
    case Waist     = 'waist';
    case Legs      = 'legs';
    case Feet      = 'feet';
    case Shield    = 'shield';
    case Wield     = 'wield';

    public static function list(): array
    {
        static $list = [
            EqSlot::Light,
            EqSlot::Head,
            EqSlot::Neck1,
            EqSlot::Neck2,
            EqSlot::Back,
            EqSlot::Chest,
            EqSlot::Shoulders,
            EqSlot::Arms,
            EqSlot::Wrist1,
            EqSlot::Wrist2,
            EqSlot::Hands,
            EqSlot::Ring1,
            EqSlot::Ring2,
            EqSlot::Waist,
            EqSlot::Legs,
            EqSlot::Feet,
            EqSlot::Shield,
            EqSlot::Wield
        ];

        return $list;
    }

    public function renderString(): string
    {
        return match($this) {
            EqSlot::Light =>     '<used as light>    ',
            EqSlot::Head =>      '<worn on head>     ',
            EqSlot::Neck1 =>     '<worn around neck> ',
            EqSlot::Neck2 =>     '<worn around neck> ',
            EqSlot::Back =>      '<worn on back>     ',
            EqSlot::Chest =>     '<worn on chest>    ',
            EqSlot::Shoulders => '<worn on shoulders>',
            EqSlot::Arms =>      '<worn on arms>     ',
            EqSlot::Wrist1 =>    '<worn around wrist>',
            EqSlot::Wrist2 =>    '<worn around wrist>',
            EqSlot::Hands =>     '<worn on hands>    ',
            EqSlot::Ring1 =>     '<worn on finger>   ',
            EqSlot::Ring2 =>     '<worn on finger>   ',
            EqSlot::Waist =>     '<worn around waist>',
            EqSlot::Legs =>      '<worn on legs>     ',
            EqSlot::Feet =>      '<worn on feet>     ',
            EqSlot::Shield =>    '<worn as shield>   ',
            EqSlot::Wield =>     '<wielded>          ',
        };
    }

    public function renderStringFiller($filler = 'your'): string
    {
        return match($this) {
            EqSlot::Light =>     "used as $filler light",
            EqSlot::Head =>      "worn on $filler head",
            EqSlot::Neck1 =>     "worn around $filler neck",
            EqSlot::Neck2 =>     "worn around $filler neck",
            EqSlot::Back =>      "worn on $filler back",
            EqSlot::Chest =>     "worn on $filler chest",
            EqSlot::Shoulders => "worn on $filler shoulders",
            EqSlot::Arms =>      "worn on $filler arms",
            EqSlot::Wrist1 =>    "worn around $filler wrist",
            EqSlot::Wrist2 =>    "worn around $filler wrist",
            EqSlot::Hands =>     "worn on $filler hands",
            EqSlot::Ring1 =>     "worn on $filler finger",
            EqSlot::Ring2 =>     "worn on $filler finger",
            EqSlot::Waist =>     "worn around $filler waist",
            EqSlot::Legs =>      "worn on $filler legs",
            EqSlot::Feet =>      "worn on $filler feet",
            EqSlot::Shield =>    "worn as $filler shield",
            EqSlot::Wield =>     "wielded as $filler weapon",
        };
    }

    public function removeMessage(): array
    {
        return match($this) {
            EqSlot::Light => [
                'You stop using @p as your lightsource.',
                '@a stops using @o as @s lightsource.'
            ],
            EqSlot::Head => [
                'You stop wearing @p on your head.',
                '@a stops wearing @o on @s head.'
            ],
            EqSlot::Neck1, EqSlot::Neck2 => [
                'You stop wearing @p around your neck.',
                '@a stops wearing @o around @s neck.'
            ],
            EqSlot::Back => [
                'You stop wearing @p on your back.',
                '@a stops wearing @o on @s back.'
            ],
            EqSlot::Chest => [
                'You stop wearing @p on your chest.',
                '@a stops wearing @o on @s chest.'
            ],
            EqSlot::Shoulders => [
                'You stop wearing @p on your shoulders.',
                '@a stops wearing @o on @s shoulders.'
            ],
            EqSlot::Arms => [
                'You stop wearing @p on your arms.',
                '@a stops wearing @o on @s arms.'
            ],
            EqSlot::Wrist1, EqSlot::Wrist2 => [
                'You stop wearing @p around your wrist',
                '@a stops wearing @o around @s wrist.'
            ],
            EqSlot::Hands => [
                'You stop wearing @p on your hands.',
                '@a stops wearing @o on @s hands.'
            ],
            EqSlot::Ring1, EqSlot::Ring2 => [
                'You stop wearing @p on your finger.',
                '@a stops wearing @o on @s finger.'
            ],
            EqSlot::Waist => [
                'You stop wearing @p around your waist.',
                '@a stops wearing @o around @s waist.'
            ],
            EqSlot::Legs => [
                'You stop wearing @p on your legs.',
                '@a stops wearing @o on @s legs.'
            ],
            EqSlot::Feet => [
                'You stop wearing @p on your feet.',
                '@a stops wearing @o on @s feet.'
            ],
            EqSlot::Shield => [
                'You stop using @p as your shield.',
                '@a stops using @o as @s shield.'
            ],
            EqSlot::Wield => [
                'You unwield @p.',
                '@a unwields @o.'
            ]
        };
    }

    public function wearMessage(): array
    {
        return match($this) {
            EqSlot::Light => [
                'You light @p and start using it as lightsource.',
                '@a lights @o and starts using it as @s lightsource.'
            ],
            EqSlot::Head => [
                'You wear @p on your head.',
                '@a wears @o on @s head.'
            ],
            EqSlot::Neck1, EqSlot::Neck2 => [
                'You wear @p around your neck.',
                '@a wears @o around @s neck.'
            ],
            EqSlot::Back => [
                'You wear @p on your back.',
                '@a wears @o on @s back.'
            ],
            EqSlot::Chest => [
                'You wear @p on your chest.',
                '@a wears @o on @s chest.'
            ],
            EqSlot::Shoulders => [
                'You wear @p on your shoulders.',
                '@a wears @o on @s shoulders.'
            ],
            EqSlot::Arms => [
                'You wear @p on your arms.',
                '@a wears @o on @s arms.'
            ],
            EqSlot::Wrist1, EqSlot::Wrist2 => [
                'You wear @p around your wrist',
                '@a wears @o around @s wrist.'
            ],
            EqSlot::Hands => [
                'You wear @p on your hands.',
                '@a wears @o on @s hands.'
            ],
            EqSlot::Ring1, EqSlot::Ring2 => [
                'You wear @p on your finger.',
                '@a wears @o on @s finger.'
            ],
            EqSlot::Waist => [
                'You wear @p around your waist.',
                '@a wears @o around @s waist.'
            ],
            EqSlot::Legs => [
                'You wear @p on your legs.',
                '@a wears @o on @s legs.'
            ],
            EqSlot::Feet => [
                'You wear @p on your feet.',
                '@a wears @o on @s feet.'
            ],
            EqSlot::Shield => [
                'You start to use @p as your shield.',
                '@a starts to use @o as @s shield.'
            ],
            EqSlot::Wield => [
                'You wield @p.',
                '@a wields @o.'
            ]
        };
    }
}
