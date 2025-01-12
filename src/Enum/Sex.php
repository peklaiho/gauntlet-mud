<?php
/**
 * Gauntlet MUD - Sexes
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Enum;

enum Sex: string
{
    case Neutral = 'N';
    case Male = 'M';
    case Female = 'F';

    public function name(): string
    {
        return match($this) {
            Sex::Neutral => 'neutral',
            Sex::Male => 'male',
            Sex::Female => 'female'
        };
    }

    public function heShe(): string
    {
        return match($this) {
            Sex::Neutral => 'it',
            Sex::Male => 'he',
            Sex::Female => 'she'
        };
    }

    public function himHer(): string
    {
        return match($this) {
            Sex::Neutral => 'it',
            Sex::Male => 'him',
            Sex::Female => 'her'
        };
    }

    public function hisHer(): string
    {
        return match($this) {
            Sex::Neutral => 'its',
            Sex::Male => 'his',
            Sex::Female => 'her'
        };
    }
}
