<?php
/**
 * Gauntlet MUD - YAML repository for shops
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Data;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

use Gauntlet\Collection;
use Gauntlet\Shop;
use Gauntlet\Util\Log;

class YamlShopRepository implements IShopRepository
{
    public function readInto(Collection $list): void
    {
        Log::info('Reading shop files.');

        $files = glob(DATA_DIR . 'shops/*.yaml');

        foreach ($files as $file) {
            Log::debug("Reading shop file $file.");
            $data = explode('---', file_get_contents($file));

            foreach ($data as $d) {
                try {
                    $shop = $this->deserialize(Yaml::parse($d));
                    $list->set($shop->getRoomId(), $shop);
                } catch (ParseException $ex) {
                    Log::error("Unable to parse shop in $file: " . $ex->getMessage());
                }
            }
        }

        $list->sort(true);

        Log::info($list->count() . ' shops read.');
    }

    private function deserialize(array $data): Shop
    {
        $shop = new Shop();

        $shop->setRoomId($data['room']);
        $shop->setShopkeeperId($data['shopkeeper']);
        $shop->setItemIds($data['items'] ?? []);
        $shop->setBuyTypes($data['buy_types'] ?? []);

        if (array_key_exists('buy_ids', $data)) {
            $buyIds = [];

            foreach ($data['buy_ids'] as $id) {
                $id = trim($id);

                if (strpos($id, '-') !== false) {
                    $idParts = explode('-', $id);
                    for ($i = $idParts[0]; $i <= $idParts[1]; $i++) {
                        $buyIds[] = $i;
                    }
                } else {
                    $buyIds[] = $id;
                }
            }

            $shop->setBuyIds($buyIds);
        }

        return $shop;
    }
}
