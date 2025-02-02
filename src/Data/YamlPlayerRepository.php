<?php
/**
 * Gauntlet MUD - YAML repository for players
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Data;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

use Gauntlet\Player;
use Gauntlet\PlayerItems;
use Gauntlet\Enum\AdminLevel;
use Gauntlet\Enum\Attribute;
use Gauntlet\Enum\PlayerClass;
use Gauntlet\Enum\Sex;
use Gauntlet\Enum\Size;
use Gauntlet\Util\Lisp;
use Gauntlet\Util\Log;

class YamlPlayerRepository implements IPlayerRepository
{
    protected string $dir;

    public function __construct(
        protected PlayerItems $playerItems
    ) {
        $this->dir = DATA_DIR . 'players/';
    }

    public function has(string $name): bool
    {
        $filename = $this->dir . $name . '.yaml';

        return is_readable($filename);
    }

    public function findByName(string $name): ?Player
    {
        $filename = $this->dir . $name . '.yaml';

        if (is_readable($filename)) {
            Log::debug("Reading player file $filename.");

            try {
                $data = Yaml::parseFile($filename);
                $player = $this->deserialize($data);

                // Evaluate custom script file for player if it exists
                $scriptFile = $this->dir . $name . '.lisp';
                if (is_readable($scriptFile)) {
                    Log::debug("Evaluating player script file $scriptFile.");
                    $code = file_get_contents($scriptFile);
                    Lisp::eval($player, "(do $code)");
                }

                return $player;
            } catch (ParseException $ex) {
                Log::error("Unable to read player file $filename: " . $ex->getMessage());
            }
        }

        return null;
    }

    public function store(Player $player): bool
    {
        $filename = $this->dir . $player->getName() . '.yaml';

        Log::debug("Writing player file $filename.");

        $data = $this->serialize($player);
        $yaml = Yaml::dump($data);

        $result = @file_put_contents($filename, $yaml);
        if (!$result) {
            Log::error("Unable to write player file $filename.");
            return false;
        }

        return true;
    }

    private function deserialize(array $data): Player
    {
        $player = new Player();

        $player->setCreationTime($data['created_on'] ?? 0);
        if (array_key_exists('admin', $data) && $data['admin']) {
            $player->setAdminLevel(AdminLevel::tryFrom($data['admin']) ?? null);
        }
        $player->setLevel($data['level'] ?? 1);
        $player->setName($data['name']);
        $player->setPassword($data['password']);
        $player->setTitle($data['title'] ?? null);
        if (array_key_exists('class', $data)) {
            $player->setClass(PlayerClass::tryFrom($data['class']) ?? PlayerClass::Warrior);
        }
        if (array_key_exists('sex', $data)) {
            $player->setSex(Sex::tryFrom($data['sex']) ?? Sex::Neutral);
        }
        if (array_key_exists('size', $data)) {
            $player->setSize(Size::tryFrom($data['size']) ?? Size::Medium);
        }
        $player->setCoins($data['coins'] ?? 0);
        $player->setBank($data['bank'] ?? 0);
        $player->setExperience($data['experience'] ?? 0);
        $player->getPreferences()->setAll($data['preferences'] ?? []);
        $player->getColorPref()->setAll($data['color_pref'] ?? []);
        if (array_key_exists('training', $data)) {
            foreach ($data['training'] as $attrName => $val) {
                $attr = Attribute::tryFrom($attrName);
                if ($attr) {
                    $player->setTraining($attr, $val);
                }
            }
        }
        $player->setHealth($data['health'] ?? 1);
        $player->setMana($data['mana'] ?? 1);
        $player->setMove($data['move'] ?? 1);
        $player->setSavedInventory($data['inventory'] ?? []);
        $player->setSavedEquipment($data['equipment'] ?? []);
        $player->setAliases($data['aliases'] ?? []);
        $player->setAcceptedRules($data['acceptedRules'] ?? false);

        return $player;
    }

    private function serialize(Player $player): array
    {
        $this->playerItems->saveItems($player);

        return [
            'created_on' => $player->getCreationTime(),
            'admin' => $player->getAdminLevel() ? $player->getAdminLevel()->value : null,
            'level' => $player->getLevel(),
            'name' => $player->getName(),
            'password' => $player->getPassword(),
            'title' => $player->getTitle(),
            'class' => $player->getClass()->value,
            'sex' => $player->getSex()->value,
            'size' => $player->getSize()->value,
            'coins' => $player->getCoins(),
            'bank' => $player->getBank(),
            'experience' => $player->getExperience(),
            'preferences' => $player->getPreferences()->getAll(),
            'color_pref' => $player->getColorPref()->getAll(),
            'training' => $player->getTraining(),
            'health' => $player->getHealth(),
            'mana' => $player->getMana(),
            'move' => $player->getMove(),
            'inventory' => $player->getSavedInventory(),
            'equipment' => $player->getSavedEquipment(),
            'aliases' => $player->getAliases(),
            'acceptedRules' => $player->getAcceptedRules(),
        ];
    }
}
