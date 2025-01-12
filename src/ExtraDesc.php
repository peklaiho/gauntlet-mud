<?php
/**
 * Gauntlet MUD - Extra description
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Trait\Keywords;

class ExtraDesc
{
    protected string $description;

    use Keywords;

    public function __construct(array $keywords, string $description)
    {
        $this->keywords = $keywords;
        $this->description = $description;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
