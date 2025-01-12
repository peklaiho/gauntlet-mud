<?php
/**
 * Gauntlet MUD - Item instance
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Collection;
use Gauntlet\Enum\ItemFlag;
use Gauntlet\Enum\ScriptType;
use Gauntlet\Template\ArmorTemplate;
use Gauntlet\Template\BulletinBoardTemplate;
use Gauntlet\Template\ContainerTemplate;
use Gauntlet\Template\ItemTemplate;
use Gauntlet\Template\WeaponTemplate;
use Gauntlet\Trait\CreationTime;
use Gauntlet\Trait\MagicNumber;

class Item extends BaseObject
{
    protected ?Room $room = null;
    protected ?Living $carrier = null;
    protected ?Item $container = null;
    protected ?Living $wearer = null;

    protected Collection $contents;

    use CreationTime;
    use MagicNumber;

    public function __construct(
        protected ItemTemplate $template,
        int $magicNum
    ) {
        $this->contents = new Collection();
        $this->setCreationTime(time());
        $this->setMagicNumber($magicNum);
    }

    public function getTemplate(): ItemTemplate
    {
        return $this->template;
    }

    public function getCarrier(): ?Living
    {
        return $this->carrier;
    }

    public function getContainer(): ?Item
    {
        return $this->container;
    }

    public function getContents(): Collection
    {
        return $this->contents;
    }

    public function getLocation(bool $detail = false): string
    {
        if ($this->getRoom()) {
            return 'in room';
        }
        if ($this->getCarrier()) {
            return 'carried';
        }
        if ($this->getContainer()) {
            if ($detail) {
                return 'in ' . $this->getContainer()->getTemplate()->getAName();
            }

            return 'in container';
        }
        if ($this->getWearer()) {
            return 'worn';
        }

        return 'unknown';
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function getWearer(): ?Living
    {
        return $this->wearer;
    }

    public function getWeight(bool $includeContents = true): float
    {
        $weight = $this->template->getWeight();

        if ($includeContents) {
            $weight += $this->getWeightOfContents();
        }

        return $weight;
    }

    public function getWeightOfContents(): float
    {
        $weight = 0;

        foreach ($this->contents as $obj) {
            $weight += $obj->getWeight(true);
        }

        return $weight;
    }

    public function isArmor(): bool
    {
        return get_class($this->template) == ArmorTemplate::class;
    }

    public function isBulletinBoard(): bool
    {
        return get_class($this->template) == BulletinBoardTemplate::class;
    }

    public function isContainer(): bool
    {
        return get_class($this->template) == ContainerTemplate::class;
    }

    public function isWeapon(): bool
    {
        return get_class($this->template) == WeaponTemplate::class;
    }

    public function isEquipment(): bool
    {
        return $this->isArmor() || $this->isWeapon();
    }

    public function isUseful(): bool
    {
        // Check if this is an useful/valuable item (not trash).

        if ($this->template->hasAnyFlag(
            ItemFlag::MonsterCorpse,
            ItemFlag::PlayerCorpse,
            ItemFlag::Trash
        )) {
            return false;
        }

        return true;
    }

    public function setCarrier(?Living $val): void
    {
        $this->carrier = $val;
    }

    public function setContainer(?Item $val): void
    {
        $this->container = $val;
    }

    public function setRoom(?Room $val): void
    {
        $this->room = $val;
    }

    public function setWearer(?Living $val): void
    {
        $this->wearer = $val;
    }

    // Override script getters: Read from template

    public function getScript(ScriptType $type): ?string
    {
        return $this->template->getScript($type);
    }

    public function getScripts(): array
    {
        return $this->template->getScripts();
    }

    #[\Override]
    public function getTechnicalName(): string
    {
        return "Item<{$this->template->getId()}:{$this->getMagicNumber()}>";
    }
}
