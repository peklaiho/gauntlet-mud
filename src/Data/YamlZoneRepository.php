<?php
/**
 * Gauntlet MUD - YAML repository for zones
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Data;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

use Gauntlet\Collection;
use Gauntlet\ZoneOp;
use Gauntlet\Enum\ScriptType;
use Gauntlet\Enum\ZoneType;
use Gauntlet\Template\ZoneTemplate;
use Gauntlet\Util\Log;
use Gauntlet\Util\StringSplitter;

class YamlZoneRepository implements IZoneRepository
{
    public function readInto(Collection $list): void
    {
        Log::info('Reading zone files.');

        $files = glob(DATA_DIR . 'zones/*.yaml');

        foreach ($files as $file) {
            Log::debug("Reading zone file $file.");
            $data = explode('---', file_get_contents($file));

            if (count($data) != 2) {
                Log::error("Zone file $file is not in correct format.");
            } else {
                try {
                    $zone = $this->deserialize(Yaml::parse($data[0]));
                } catch (ParseException $ex) {
                    Log::error("Unable to parse zone $file: " . $ex->getMessage());
                    continue;
                }

                $this->parseOps($zone, $data[1]);
                $list->set($zone->getId(), $zone);
            }
        }

        $list->sort(true);

        Log::info($list->count() . ' zones read.');
    }

    private function deserialize(array $data): ZoneTemplate
    {
        $zone = new ZoneTemplate();

        if (array_key_exists('type', $data)) {
            $type = ZoneType::tryFrom($data['type']);
            if ($type) {
                $zone->setType($type);
            } else {
                Log::error('Zone ' . $data['id'] . ' has invalid zone type.');
            }
        }

        $zone->setId($data['id']);
        $zone->setName($data['name']);
        if (array_key_exists('interval', $data)) {
            $zone->setInterval($data['interval'] * 60); // convert minutes to seconds
        }
        $zone->setRange($data['range']);

        foreach ($data['scripts'] ?? [] as $key => $value) {
            $type = ScriptType::tryFrom($key);

            if ($type) {
                $zone->setScript($type, $value);
            } else {
                Log::error('Zone ' . $data['id'] . ' has invalid script type: ' . $key);
            }
        }

        return $zone;
    }

    private function parseOps(ZoneTemplate $zone, string $data): void
    {
        // Read lines
        $lines = StringSplitter::splitByNewline($data);

        // Skip empty lines and comments
        $ops = array_filter($lines, function ($txt) {
            $txt = trim($txt);
            return strlen($txt) > 0 && !str_starts_with($txt, '#');
        });

        // Read all to temporary list first and validate indentation
        $list = [];
        foreach ($ops as $opData) {
            $indent = $this->readIndent($opData);

            if ($indent < 0 || $indent % 2 != 0) {
                if ($indent < 0) {
                    Log::error("Zone op indented with tabs: $opData");
                } else {
                    Log::error("Zone op contains invalid indentation level ($indent spaces): $opData");
                }

                return;
            }

            $list[] = [$indent / 2, trim($opData)];
        }

        // Process them according to indentation levels
        $index = 0;
        $list = $this->processOps($list, $index, 0);

        if ($list) {
            $zone->setOps($list);
        }
    }

    private function processOps(array $list, int &$index, int $level): ?array
    {
        $result = [];

        for (; $index < count($list); ) {
            $op = $list[$index];

            if ($op[0] == $level) {
                // Same level, add to this list
                $result[] = new ZoneOp($op[1]);
                $index++;
            } elseif ($op[0] == $level + 1) {
                // One level higher, children of last op
                $result[count($result) - 1]->setChildren($this->processOps($list, $index, $level + 1));
            } elseif ($op[0] >= $level + 2) {
                // Error
                Log::error("Zone op indentation increases more than 1 level: " . $op[1]);
                return null;
            } else {
                // Lesser level, break here
                break;
            }
        }

        return $result;
    }

    private function readIndent(string $txt): int
    {
        for ($i = 0; $i < strlen($txt); $i++) {
            $c = substr($txt, $i, 1);

            // Do not allow tabs for now
            if ($c == "\t") {
                return -1;
            } elseif ($c != ' ') {
                return $i;
            }
        }

        // This should not happen
        return -2;
    }
}
