<?php
/**
 * Gauntlet MUD - Sizes
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Enum;

enum Size: string
{
    case Mini = 'mini';
    case Tiny = 'tiny';
    case Small = 'small';
    case Medium = 'medium';
    case Large = 'large';
    case Huge = 'huge';
    case Enormous = 'enormous';

    public function getMonsterWeight(): int
    {
        return match($this) {
            Size::Mini => 2,
            Size::Tiny => 10,
            Size::Small => 20,
            Size::Medium => 50,
            Size::Large => 100,
            Size::Huge => 150,
            Size::Enormous => 200
        };
    }

    public function getPlayerWeight(Sex $sex): int
    {
        $weights = match($this) {
            Size::Mini => [30, 40],
            Size::Tiny => [40, 50],
            Size::Small => [50, 60],
            Size::Medium => [60, 70],
            Size::Large => [70, 90],
            Size::Huge => [80, 100],
            Size::Enormous => [90, 120]
        };

        return $weights[$sex == Sex::Male ? 1 : 0];
    }
}
