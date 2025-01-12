<?php
/**
 * Gauntlet MUD - Base class for templates
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Template;

use Gauntlet\Enum\Modifier;
use Gauntlet\Trait\Flags;
use Gauntlet\Trait\Keywords;
use Gauntlet\Trait\Scripts;

abstract class BaseTemplate
{
    protected int $id;
    protected string $article;
    protected string $name;
    protected string $plural;
    protected ?string $sdesc = null;
    protected ?string $ldesc = null;
    protected array $mods = [];
    protected int $count = 0;

    use Flags;
    use Keywords;
    use Scripts;

    public function getId(): int
    {
        return $this->id;
    }

    public function getArticle(): string
    {
        return $this->article;
    }

    public function getAName(int $count = 1): string
    {
        if ($count > 1) {
            return $count . ' ' . $this->getPlural();
        }

        if ($this->article) {
            return $this->article . ' ' . $this->name;
        }

        return $this->name;
    }

    public function getTheName(int $count = 1): string
    {
        if ($count > 1) {
            return $count . ' ' . $this->getPlural();
        }

        if ($this->article) {
            return 'the ' . $this->name;
        }

        return $this->name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPlural(): string
    {
        return $this->plural;
    }

    public function getShortDesc(): ?string
    {
        return $this->sdesc;
    }

    public function getLongDesc(): ?string
    {
        return $this->ldesc;
    }

    public function getMod(Modifier $mod): float
    {
        return $this->mods[$mod->value] ?? 0;
    }

    public function getMods(): array
    {
        return $this->mods;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function incCount(): void
    {
        $this->count++;
    }

    public function decCount(): void
    {
        $this->count--;
    }

    public function setId(int $val): void
    {
        $this->id = $val;
    }

    public function setArticle(string $val): void
    {
        $this->article = $val;
    }

    public function setName(string $val): void
    {
        $this->name = $val;
    }

    public function setPlural(string $val): void
    {
        $this->plural = $val;
    }

    public function setShortDesc(?string $val): void
    {
        $this->sdesc = $val;
    }

    public function setLongDesc(?string $val): void
    {
        $this->ldesc = $val;
    }

    public function setMod(Modifier $mod, float $val): void
    {
        $this->mods[$mod->value] = $val;
    }
}
