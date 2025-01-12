<?php
/**
 * Gauntlet MUD - Trait for attribute training
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Trait;

use Gauntlet\Enum\Attribute;

trait Training
{
    protected array $training = [];

    public function getTraining(): array
    {
        return $this->training;
    }

    public function getTrainedAttribute(Attribute $attr): int
    {
        return $this->training[$attr->value] ?? 0;
    }

    public function getRemainingTraining(): int
    {
        return $this->getLevel() - array_sum($this->training);
    }

    public function trainAttribute(Attribute $attr): void
    {
        $this->setTraining($attr, $this->getTrainedAttribute($attr) + 1);
    }

    public function setTraining(Attribute $attr, int $val): void
    {
        $this->training[$attr->value] = $val;
    }
}
