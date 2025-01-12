<?php
/**
 * Gauntlet MUD - Shop instance
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Enum\ItemFlag;

class Shop extends BaseObject
{
    protected int $roomId;
    protected int $shopkeeperId;
    protected array $itemIds = [];
    protected array $buyTypes = [];

    // Return how much the shop pays for the item,
    // if the shop wants to buy it. Value <= 0 means
    // the shop is not interested.
    public function paysForItem(Item $item): int
    {
        // Do not accept corpses :)
        if ($item->getTemplate()->hasAnyFlag(
            ItemFlag::MonsterCorpse,
            ItemFlag::PlayerCorpse
        )) {
            return -1;
        }

        // Pay 20%
        $buyValue = intdiv($item->getTemplate()->getCost(), 5);

        if (($item->isWeapon() && in_array('weapon', $this->buyTypes)) ||
            ($item->isArmor() && in_array('armor', $this->buyTypes)) ||
            ($item->isContainer() && in_array('container', $this->buyTypes))) {
            return $buyValue;
        }

        return -1;
    }

    public function getRoomId(): int
    {
        return $this->roomId;
    }

    public function getShopkeeperId(): int
    {
        return $this->shopkeeperId;
    }

    public function getItemIds(): array
    {
        return $this->itemIds;
    }

    public function getBuyTypes(): array
    {
        return $this->buyTypes;
    }

    public function setRoomId(int $val): void
    {
        $this->roomId = $val;
    }

    public function setShopkeeperId(int $val): void
    {
        $this->shopkeeperId = $val;
    }

    public function setItemIds(array $val): void
    {
        $this->itemIds = $val;
    }

    public function setBuyTypes(array $val): void
    {
        $this->buyTypes = $val;
    }

    #[\Override]
    public function getTechnicalName(): string
    {
        return "Shop<{$this->roomId}>";
    }
}
