<?php
/**
 * Gauntlet MUD - Trait for affections
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Trait;

use Gauntlet\Affection;
use Gauntlet\Enum\AffectionType;
use Gauntlet\Enum\Skill;
use Gauntlet\Enum\Spell;

trait Affections
{
    protected array $affections = [];

    public function addAffection(Affection $aff): void
    {
        $this->affections[] = $aff;
    }

    public function clearAffections(): void
    {
        $this->affections = [];
    }

    public function getAffections(): array
    {
        return $this->affections;
    }

    public function getSkillAffection(Skill $skill): ?Affection
    {
        return $this->findAffection(AffectionType::Skill, $skill);
    }

    public function getSpellAffection(Spell $spell): ?Affection
    {
        return $this->findAffection(AffectionType::Spell, $spell);
    }

    public function updateAffections(): void
    {
        $now = time();

        for ($i = 0; $i < count($this->affections); ) {
            $aff = $this->affections[$i];
            if ($now >= $aff->getUntil()) {
                array_splice($this->affections, $i, 1);
                if ($aff->getCallback()) {
                    ($aff->getCallback())();
                }
            } else {
                $i++;
            }
        }
    }

    private function findAffection(AffectionType $type, Spell|Skill $source)
    {
        foreach ($this->affections as $aff) {
            if ($aff->getType() == $type && $aff->getSource() == $source) {
                return $aff;
            }
        }

        return null;
    }
}
