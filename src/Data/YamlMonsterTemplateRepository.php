<?php
/**
 * Gauntlet MUD - YAML repository for monsters
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Data;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

use Gauntlet\AmbientMessage;
use Gauntlet\Collection;
use Gauntlet\Enum\Attack;
use Gauntlet\Enum\Damage;
use Gauntlet\Enum\Modifier;
use Gauntlet\Enum\MonsterFlag;
use Gauntlet\Enum\ScriptType;
use Gauntlet\Enum\Sex;
use Gauntlet\Enum\Size;
use Gauntlet\MonsterAction\Handler as ActionsHandler;
use Gauntlet\Template\MonsterTemplate;
use Gauntlet\Util\Log;

class YamlMonsterTemplateRepository implements IMonsterTemplateRepository
{
    public function readInto(Collection $list): void
    {
        Log::info('Reading monster files.');

        $files = glob(DATA_DIR . 'monsters/*.yaml');

        foreach ($files as $file) {
            Log::debug("Reading monster file $file.");
            $data = explode('---', file_get_contents($file));

            foreach ($data as $d) {
                try {
                    $monster = $this->deserialize(Yaml::parse($d));
                    $list->set($monster->getId(), $monster);
                } catch (ParseException $ex) {
                    Log::error("Unable to parse monster in $file: " . $ex->getMessage());
                }
            }
        }

        $list->sort(true);

        Log::info($list->count() . ' monsters read.');
    }

    private function deserialize(array $data): MonsterTemplate
    {
        $monster = new MonsterTemplate();

        $monster->setId($data['id']);
        $monster->setLevel($data['level'] ?? 1);
        $monster->setArticle($data['article'] ?? '');
        $monster->setName($data['name']);
        $monster->setPlural($data['plural'] ?? $data['name'] . 's');
        $monster->setKeywords($data['keywords']);
        $monster->setShortDesc($data['short_desc'] ?? null);
        $monster->setLongDesc($data['long_desc'] ?? null);
        $monster->setFactionId($data['faction'] ?? null);
        $monster->setAvoidRooms($data['avoid_rooms'] ?? []);

        if (array_key_exists('sex', $data)) {
            $sex = Sex::tryFrom($data['sex']);
            if ($sex) {
                $monster->setSex($sex);
            } else {
                Log::error('Monster ' . $data['id'] . ' has invalid sex: ' . $data['sex']);
            }
        }

        if (array_key_exists('size', $data)) {
            $size = Size::tryFrom($data['size']);
            if ($size) {
                $monster->setSize($size);
            } else {
                Log::error('Monster ' . $data['id'] . ' has invalid size: ' . $data['size']);
            }
        }

        if (array_key_exists('attack', $data)) {
            $attType = Attack::tryFrom($data['attack']);
            if ($attType) {
                $monster->setAttackType($attType);
            } else {
                Log::error('Monster ' . $data['id'] . ' has invalid attack type: ' . $data['attack']);
            }
        }

        if (array_key_exists('dam_type', $data)) {
            $damType = Damage::tryFrom($data['dam_type']);
            if ($damType) {
                $monster->setDamageType($damType);
            } else {
                Log::error('Monster ' . $data['id'] . ' has invalid damage type: ' . $data['dam_type']);
            }
        }

        $monster->setNumAttacks($data['attacks'] ?? 1);

        foreach ($data['mods'] ?? [] as $name => $val) {
            $mod = Modifier::tryFrom($name);
            if ($mod) {
                $monster->setMod($mod, $val);
            } else {
                Log::error('Monster ' . $data['id'] . ' has invalid modifier: ' . $name);
            }
        }

        foreach ($data['flags'] ?? [] as $name) {
            $flag = MonsterFlag::tryFrom($name);
            if ($flag) {
                $monster->addFlag($flag);
            } else {
                Log::error('Monster ' . $data['id'] . ' has invalid flag: ' . $name);
            }
        }

        foreach ($data['scripts'] ?? [] as $key => $value) {
            $type = ScriptType::tryFrom($key);

            if ($type) {
                $monster->setScript($type, $value);
            } else {
                Log::error('Monster ' . $data['id'] . ' has invalid script type: ' . $key);
            }
        }

        foreach ($data['ambient'] ?? [] as $amb) {
            if (array_key_exists('sex', $amb)) {
                $sex = Sex::tryFrom($amb['sex']);
                if (!$sex) {
                    Log::error('Monster ' . $data['id'] . ' has invalid sex for ambient message: ' . $amb['sex']);
                }
            } else {
                $sex = null;
            }

            $ambient = new AmbientMessage($amb['room'], $amb['victim'] ?? null, $sex);
            $monster->addAmbientMessage($ambient);
        }

        return $monster;
    }
}
