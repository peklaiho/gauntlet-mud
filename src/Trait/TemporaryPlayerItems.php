<?php
/**
 * Gauntlet MUD - Trait for temporary player items
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Trait;

trait TemporaryPlayerItems
{
    protected array $savedInventory = [];
    protected array $savedEquipment = [];

    public function getSavedInventory(): array
    {
        return $this->savedInventory;
    }

    public function getSavedEquipment(): array
    {
        return $this->savedEquipment;
    }

    public function setSavedInventory(array $items): void
    {
        $this->savedInventory = $items;
    }

    public function setSavedEquipment(array $items): void
    {
        $this->savedEquipment = $items;
    }
}
