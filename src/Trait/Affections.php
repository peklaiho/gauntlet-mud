<?php
/**
 * Gauntlet MUD - Trait for affections
 * Copyright (C) 2017-2026 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Trait;

use Gauntlet\Affection;
use Gauntlet\Item;
use Gauntlet\Living;
use Gauntlet\Enum\AffectionType;
use Gauntlet\Enum\Modifier;
use Gauntlet\Enum\Skill;
use Gauntlet\Enum\Spell;
use Gauntlet\Util\Log;

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
        for ($i = 0; $i < count($this->affections); ) {
            $aff = $this->affections[$i];
            if ($aff->tick()) {
                array_splice($this->affections, $i, 1);
                if (($aff->getOwner() instanceof Living) && $aff->getOwner()->isPlayer() && $aff->getEndMessage()) {
                    $aff->getOwner()->outln($aff->getEndMessage());
                }
            } else {
                $i++;
            }
        }
    }

    public function serializeAffections(): array
    {
        $list = [];

        foreach ($this->affections as $aff) {
            $list[] = [
                'type' => $aff->getType()->value,
                'source' => $aff->getSource()->value,
                'total_ticks' => $aff->getTotalTicks(),
                'elapsed_ticks' => $aff->getElapsedTicks(),
                'end_message' => $aff->getEndMessage(),
                'modifiers' => $aff->getMods(),
            ];
        }

        return $list;
    }

    public function unserializeAffections(Living|Item $owner, array $list): void
    {
        foreach ($list as $info) {
            $type = AffectionType::tryFrom($info['type']);

            if (!$type) {
                Log::warn('Unknown affection type: ' . $info['type']);
                continue;
            }

            if ($type === AffectionType::Spell) {
                $source = Spell::tryFrom($info['source']);
            } else {
                $source = Skill::tryFrom($info['source']);
            }

            if (!$source) {
                Log::warn('Unknown affection source: ' . $info['source']);
                continue;
            }

            $aff = new Affection($owner, $type, $source, $info['total_ticks'], $info['end_message']);
            $aff->setElapsedTicks($info['elapsed_ticks']);

            foreach ($info['modifiers'] ?? [] as $modName => $value) {
                $mod = Modifier::tryFrom($modName);

                if ($mod) {
                    $aff->setMod($mod, $value);
                } else {
                    Log::warn("Unknown modifier $modName for affection");
                }
            }

            $this->affections[] = $aff;
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
