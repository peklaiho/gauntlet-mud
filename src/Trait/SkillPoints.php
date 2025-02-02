<?php
/**
 * Gauntlet MUD - Trait for skill points
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Trait;

use Gauntlet\Enum\Skill;

trait SkillPoints
{
    protected array $skillPoints = [];

    public function getSkillPoints(): array
    {
        return $this->skillPoints;
    }

    public function getSkillLevel(Skill $skill): int
    {
        return $this->skillPoints[$skill->value] ?? 0;
    }

    public function getRemainingSkillPoints(): int
    {
        return $this->getLevel() - array_sum($this->skillPoints);
    }

    public function increaseSkillLevel(Skill $skill): void
    {
        $this->setSkillLevel($skill, $this->getSkillPoints($skill) + 1);
    }

    public function setSkillLevel(Skill $skill, int $val): void
    {
        $this->skillPoints[$skill->value] = $val;
    }
}
