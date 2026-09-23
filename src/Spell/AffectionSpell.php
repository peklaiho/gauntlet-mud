<?php
/**
 * Gauntlet MUD - Spells that create affections
 * Copyright (C) 2017-2026 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Spell;

use Gauntlet\Affection;
use Gauntlet\Item;
use Gauntlet\Living;
use Gauntlet\Enum\AffectionType;
use Gauntlet\Enum\Modifier;
use Gauntlet\Enum\Spell;
use Gauntlet\Util\LivingFinder;

class AffectionSpell extends BaseSpell
{
    protected ?Spell $lowerTierSpell = null;
    protected ?Spell $higherTierSpell = null;

    public function __construct(
        protected Spell $spell,
        protected float $manaCost,
        protected array $mods,
        protected int $duration,
        protected string $startMessage,
        protected string $endMessage
    ) {

    }

    public function manaCost(): float
    {
        return $this->manaCost;
    }

    public function findTarget(Living $caster, ?string $targetName): Living|Item|null
    {
        // No target given, default to caster
        if (!$targetName) {
            return $caster;
        }

        $lists = [$caster->getRoom()->getLiving()];
        $target = (new LivingFinder($caster, $lists))
            ->find($targetName);
        return $target;
    }

    public function cast(Living $caster, Living|Item $target): void
    {
        if ($target instanceof Living && $target->isPlayer()) {
            $target->outln($this->startMessage);
        }

        // Remove old affections of this spell or a lower-level version
        $target->removeSpellAffection($this->spell);
        if ($this->lowerTierSpell) {
            $target->removeSpellAffection($this->lowerTierSpell);
        }

        $aff = new Affection($target, AffectionType::Spell, $this->spell, $this->duration, $this->endMessage);

        foreach ($this->mods as $modName => $value) {
            $aff->setMod(Modifier::from($modName), $value);
        }

        $target->addAffection($aff);
    }

    public function getLowerTierSpell(): ?Spell
    {
        return $this->lowerTierSpell;
    }

    public function getHigherTierSpell(): ?Spell
    {
        return $this->higherTierSpell;
    }

    public function setLowerTierSpell(Spell $spell): void
    {
        $this->lowerTierSpell = $spell;
    }

    public function setHigherTierSpell(Spell $spell): void
    {
        $this->higherTierSpell = $spell;
    }
}
