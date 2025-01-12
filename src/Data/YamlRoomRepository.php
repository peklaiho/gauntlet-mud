<?php
/**
 * Gauntlet MUD - YAML repository for rooms
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Data;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

use Gauntlet\AmbientMessage;
use Gauntlet\Collection;
use Gauntlet\ExtraDesc;
use Gauntlet\Enum\Direction;
use Gauntlet\Enum\ExitFlag;
use Gauntlet\Enum\RoomFlag;
use Gauntlet\Enum\ScriptType;
use Gauntlet\Enum\Sex;
use Gauntlet\Enum\Terrain;
use Gauntlet\Template\RoomTemplate;
use Gauntlet\Template\RoomExitTemplate;
use Gauntlet\Util\Log;

class YamlRoomRepository implements IRoomRepository
{
    public function readInto(Collection $list, bool $logging = true): void
    {
        if ($logging) {
            Log::info('Reading room files.');
        }

        $files = glob(DATA_DIR . 'rooms/*.yaml');

        foreach ($files as $file) {
            Log::debug("Reading room file $file.");
            $data = explode('---', file_get_contents($file));

            foreach ($data as $d) {
                try {
                    $room = $this->deserialize(Yaml::parse($d));
                    $list->set($room->getId(), $room);
                } catch (ParseException $ex) {
                    Log::error("Unable to parse room in $file: " . $ex->getMessage());
                }
            }
        }

        $list->sort(true);

        if ($logging) {
            Log::info($list->count() . ' rooms read.');
        }
    }

    private function deserialize(array $data): RoomTemplate
    {
        $room = new RoomTemplate();

        $room->setId($data['id']);
        $room->setName($data['name']);
        $room->setLongDesc($data['description'] ?? null);

        foreach ($data['exits'] ?? [] as $dirName => $exitInfo) {
            $dir = Direction::tryFrom($dirName);

            if ($dir) {
                $exit = null;

                if (is_array($exitInfo)) {
                    if (array_key_exists('to', $exitInfo)) {
                        $exit = new RoomExitTemplate($exitInfo['to']);
                        $exit->setDoorName($exitInfo['name'] ?? null);
                        $exit->setKeyId($exitInfo['key'] ?? null);

                        foreach ($exitInfo['flags'] ?? [] as $name) {
                            $flag = ExitFlag::tryFrom($name);
                            if ($flag) {
                                $exit->addFlag($flag);
                            } else {
                                Log::error('Exit in room ' . $data['id'] . ' has invalid flag: ' . $name);
                            }
                        }

                        foreach ($exitInfo['scripts'] ?? [] as $key => $value) {
                            $type = ScriptType::tryFrom($key);

                            if ($type) {
                                $exit->setScript($type, $value);
                            } else {
                                Log::error('Exit in room ' . $data['id'] . ' has invalid script type: ' . $key);
                            }
                        }
                    } else {
                        Log::error('Room ' . $data['id'] . ' has invalid exit.');
                    }
                } else {
                    $exit = new RoomExitTemplate($exitInfo);
                }

                if ($exit) {
                    $room->setExit($dir, $exit);
                }
            } else {
                Log::error('Room ' . $data['id'] . ' has invalid exit direction: ' . $dirName);
            }
        }

        foreach ($data['extra'] ?? [] as $extra) {
            $extraDesc = new ExtraDesc($extra['keywords'], $extra['description']);
            $room->addExtraDesc($extraDesc);
        }

        foreach ($data['flags'] ?? [] as $name) {
            $flag = RoomFlag::tryFrom($name);
            if ($flag) {
                $room->addFlag($flag);
            } else {
                Log::error('Room ' . $data['id'] . ' has invalid flag: ' . $name);
            }
        }

        if (array_key_exists('terrain', $data)) {
            $terrain = Terrain::tryFrom($data['terrain']);
            if ($terrain) {
                $room->setTerrain($terrain);
            } else {
                Log::error('Room ' . $data['id'] . ' has invalid terrain type: ' . $data['terrain']);
            }
        }

        foreach ($data['scripts'] ?? [] as $key => $value) {
            $type = ScriptType::tryFrom($key);

            if ($type) {
                $room->setScript($type, $value);
            } else {
                Log::error('Room ' . $data['id'] . ' has invalid script type: ' . $key);
            }
        }

        foreach ($data['ambient'] ?? [] as $amb) {
            if (array_key_exists('sex', $amb)) {
                $sex = Sex::tryFrom($amb['sex']);
                if (!$sex) {
                    Log::error('Room ' . $data['id'] . ' has invalid sex for ambient message: ' . $amb['sex']);
                }
            } else {
                $sex = null;
            }

            $ambient = new AmbientMessage($amb['room'], $amb['victim'] ?? null, $sex);
            $room->addAmbientMessage($ambient);
        }

        return $room;
    }
}
