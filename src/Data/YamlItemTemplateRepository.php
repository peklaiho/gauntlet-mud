<?php
/**
 * Gauntlet MUD - YAML repository for items
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Data;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

use Gauntlet\Collection;
use Gauntlet\Enum\Attack;
use Gauntlet\Enum\Damage;
use Gauntlet\Enum\EqSlot;
use Gauntlet\Enum\ItemFlag;
use Gauntlet\Enum\Modifier;
use Gauntlet\Enum\ScriptType;
use Gauntlet\Template\ArmorTemplate;
use Gauntlet\Template\BulletinBoardTemplate;
use Gauntlet\Template\ContainerTemplate;
use Gauntlet\Template\ItemTemplate;
use Gauntlet\Template\WeaponTemplate;
use Gauntlet\Util\Log;

class YamlItemTemplateRepository implements IItemTemplateRepository
{
    public function readInto(Collection $list): void
    {
        Log::info('Reading item files.');

        $files = glob(DATA_DIR . 'items/*.yaml');

        foreach ($files as $file) {
            Log::debug("Reading item file $file.");
            $data = explode('---', file_get_contents($file));

            foreach ($data as $d) {
                try {
                    $item = $this->deserialize(Yaml::parse($d));
                    $list->set($item->getId(), $item);
                } catch (ParseException $ex) {
                    Log::error("Unable to parse item in $file: " . $ex->getMessage());
                }
            }
        }

        $list->sort(true);

        Log::info($list->count() . ' items read.');
    }

    private function deserialize(array $data): ItemTemplate
    {
        $type = $data['type'] ?? null;

        if ($type == 'weapon') {
            $item = new WeaponTemplate();

            if (array_key_exists('attack', $data)) {
                $attType = Attack::tryFrom($data['attack']);
                if ($attType) {
                    $item->setAttackType($attType);
                } else {
                    Log::error('Weapon ' . $data['id'] . ' has invalid attack type: ' . $data['attack']);
                }
            }

            if (array_key_exists('dam_type', $data)) {
                $damType = Damage::tryFrom($data['dam_type']);
                if ($damType) {
                    $item->setDamageType($damType);
                } else {
                    Log::error('Weapon ' . $data['id'] . ' has invalid damage type: ' . $data['dam_type']);
                }
            }

            $item->setMinDamage($data['min_dam'] ?? 1);
            $item->setMaxDamage($data['max_dam'] ?? 1);
        } elseif ($type == 'armor') {
            $item = new ArmorTemplate();
        } elseif ($type == 'container') {
            $item = new ContainerTemplate();
            $item->setCapacity($data['capacity'] ?? 0);
        } elseif ($type == 'bulletin_board') {
            $item = new BulletinBoardTemplate();
        } else {
            $item = new ItemTemplate();
        }

        $item->setId($data['id']);
        $item->setArticle($data['article'] ?? '');
        $item->setName($data['name']);
        $item->setPlural($data['plural'] ?? $data['name'] . 's');
        $item->setKeywords($data['keywords']);
        $item->setShortDesc($data['short_desc'] ?? null);
        $item->setLongDesc($data['long_desc'] ?? null);
        $item->setWeight($data['weight'] ?? 0);
        $item->setCost($data['cost'] ?? 0);

        foreach ($data['worn'] ?? [] as $name) {
            $slot = EqSlot::tryFrom($name);
            if ($slot) {
                $item->addSlot($slot);
            } else {
                Log::error('Item ' . $data['id'] . ' has invalid equipment slot: ' . $name);
            }
        }

        foreach ($data['mods'] ?? [] as $name => $val) {
            $mod = Modifier::tryFrom($name);
            if ($mod) {
                $item->setMod($mod, $val);
            } else {
                Log::error('Item ' . $data['id'] . ' has invalid modifier: ' . $name);
            }
        }

        foreach ($data['flags'] ?? [] as $name) {
            $flag = ItemFlag::tryFrom($name);
            if ($flag) {
                $item->addFlag($flag);
            } else {
                Log::error('Item ' . $data['id'] . ' has invalid flag: ' . $name);
            }
        }

        foreach ($data['scripts'] ?? [] as $key => $value) {
            $type = ScriptType::tryFrom($key);

            if ($type) {
                $item->setScript($type, $value);
            } else {
                Log::error('Item ' . $data['id'] . ' has invalid script type: ' . $key);
            }
        }

        return $item;
    }
}
