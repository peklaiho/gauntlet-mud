<?php
/**
 * Gauntlet MUD - Monster instance
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Enum\Attack;
use Gauntlet\Enum\Damage;
use Gauntlet\Enum\Fondness;
use Gauntlet\Enum\Modifier;
use Gauntlet\Enum\MonsterFlag;
use Gauntlet\Enum\ScriptType;
use Gauntlet\Enum\Sex;
use Gauntlet\Enum\Size;
use Gauntlet\Template\MonsterTemplate;
use Gauntlet\Trait\CreationTime;
use Gauntlet\Trait\MagicNumber;

class Monster extends Living
{
    use CreationTime;
    use MagicNumber;

    public function __construct(
        protected MonsterTemplate $template,
        int $magicNum
    ) {
        parent::__construct();
        $this->setHealth($this->getMaxHealth());
        $this->setCreationTime(time());
        $this->setMagicNumber($magicNum);
    }

    #[\Override]
    public function canSee(Living $other): bool
    {
        // Fow now monsters see everyone
        return true;
    }

    #[\Override]
    public function canSeeItem(Item $item): bool
    {
        // For now monsters see all items
        return true;
    }

    #[\Override]
    public function canSeeRoom(): bool
    {
        // For now monsters always see rooms
        return true;
    }

    #[\Override]
    public function shouldFollow(Living $other): bool
    {
        // For now monsters do not follow anyone
        return false;
    }

    public function shouldAssist(Living $other): bool
    {
        $target = $other->getTarget();
        if (!$target) {
            return false;
        }

        $fond1 = $this->getFondness($other)->value;
        $fond2 = $this->getFondness($target)->value;

        return $fond1 > $fond2;
    }

    #[\Override]
    public function getName(): string
    {
        return $this->template->getName();
    }

    #[\Override]
    public function getSex(): Sex
    {
        return $this->template->getSex();
    }

    #[\Override]
    public function getSize(): Size
    {
        return $this->template->getSize();
    }

    #[\Override]
    public function getWeight(): float
    {
        return $this->getSize()->getMonsterWeight();
    }

    #[\Override]
    public function getExperience(): int
    {
        // No exp for shopkeepers
        if ($this->template->hasFlag(MonsterFlag::Shopkeeper)) {
            return 0;
        }

        // Base
        $value = MonsterStats::getExperience($this->getLevel());

        // Bonus from template
        $value += $this->getMod(Modifier::Experience);

        return $value;
    }

    #[\Override]
    public function getMaxHealth(): float
    {
        // Base
        $value = MonsterStats::getBaseHealth($this->getLevel());

        // Bonus from constitution
        $value += $this->getCon(false) * 20;

        // Bonus from template and equipment
        $value += $this->getMod(Modifier::Health);

        return $value;
    }

    #[\Override]
    public function getMod(Modifier $mod): float
    {
        // We override this function because monsters can have
        // mods on their own template, in addition to equipment.

        return $this->template->getMod($mod) + parent::getMod($mod);
    }

    #[\Override]
    public function getLevel(): int
    {
        return $this->template->getLevel();
    }

    public function getTemplate(): MonsterTemplate
    {
        return $this->template;
    }

    public function getFondness(Living $other): Fondness
    {
        if ($other->isPlayer()) {
            // Maybe later add more logic here
            return Fondness::Neutral;
        }

        $thisFaction = $this->getTemplate()->getFaction();
        $otherFaction = $other->getTemplate()->getFaction();

        // Neutral if either one has no faction
        if (!$thisFaction || !$otherFaction) {
            return Fondness::Neutral;
        }

        // Always loved if same faction
        if ($thisFaction === $otherFaction) {
            return Fondness::Loved;
        }

        return $thisFaction->getFondness($otherFaction);
    }

    #[\Override]
    public function regenerate(): void
    {
        // TODO: implement something sensible here
    }

    // Attacks and damage

    #[\Override]
    public function getAttackType(): Attack
    {
        $weapon = $this->getWeapon();

        if ($weapon) {
            return $weapon->getTemplate()->getAttackType();
        }

        return $this->template->getAttackType();
    }

    #[\Override]
    public function getDamageType(): Damage
    {
        $weapon = $this->getWeapon();

        if ($weapon) {
            return $weapon->getTemplate()->getDamageType();
        }

        return $this->template->getDamageType();
    }

    #[\Override]
    public function getNumAttacks(): int
    {
        return $this->template->getNumAttacks();
    }

    #[\Override]
    public function getMinDamage(): float
    {
        $weapon = $this->getWeapon();

        if ($weapon) {
            $damage = $weapon->getTemplate()->getMinDamage();
        } else {
            $damage = 2;
        }

        return $damage * MonsterStats::getDamageMultiplier($this->getLevel());
    }

    #[\Override]
    public function getMaxDamage(): float
    {
        $weapon = $this->getWeapon();

        if ($weapon) {
            $damage = $weapon->getTemplate()->getMaxDamage();
        } else {
            $damage = 4;
        }

        return $damage * MonsterStats::getDamageMultiplier($this->getLevel());
    }

    #[\Override]
    public function getBonusDamage(): float
    {
        // Override this function to include base damage bonus

        return MonsterStats::getDamageBonus($this->getLevel()) + parent::getBonusDamage();
    }

    // Override script getters: Read from template

    #[\Override]
    public function getScript(ScriptType $type): ?string
    {
        return $this->template->getScript($type);
    }

    #[\Override]
    public function getScripts(): array
    {
        return $this->template->getScripts();
    }

    #[\Override]
    public function getTechnicalName(): string
    {
        return "Monster<{$this->template->getId()}:{$this->getMagicNumber()}>";
    }
}
