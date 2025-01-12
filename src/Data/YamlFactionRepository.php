<?php
/**
 * Gauntlet MUD - YAML repository for factions
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Data;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

use Gauntlet\Collection;
use Gauntlet\Faction;
use Gauntlet\Enum\Fondness;
use Gauntlet\Util\Log;

class YamlFactionRepository implements IFactionRepository
{
    public function readInto(Collection $list): void
    {
        Log::info('Reading faction files.');

        $files = glob(DATA_DIR . 'factions/*.yaml');

        foreach ($files as $file) {
            Log::debug("Reading faction file $file.");
            $data = explode('---', file_get_contents($file));

            foreach ($data as $d) {
                try {
                    $faction = $this->deserialize(Yaml::parse($d));
                    $list->set($faction->getId(), $faction);
                } catch (ParseException $ex) {
                    Log::error("Unable to parse faction in $file: " . $ex->getMessage());
                }
            }
        }

        $list->sort(true);

        Log::info($list->count() . ' factions read.');
    }

    private function deserialize(array $data): Faction
    {
        $faction = new Faction();

        $faction->setId($data['id']);
        $faction->setName($data['name']);

        foreach ($data['fondness'] ?? [] as $key => $value) {
            $fondness = Fondness::tryFrom($value);
            if ($fondness) {
                $faction->setFondness($key, $fondness);
            } else {
                Log::error('Faction has invalid value for fondness: ' . $value);
            }
        }

        return $faction;
    }
}
