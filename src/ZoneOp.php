<?php
/**
 * Gauntlet MUD - Zone operation
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Util\StringSplitter;

class ZoneOp extends Collection
{
    protected array $children = [];

    public function __construct(
        protected string $raw
    ) {
        parent::__construct(StringSplitter::splitBySpace($raw));
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function getRaw(): string
    {
        return $this->raw;
    }

    public function getType(): string
    {
        return $this->data[0];
    }

    public function setChildren(array $children): void
    {
        $this->children = $children;
    }
}
