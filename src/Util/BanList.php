<?php
/**
 * Gauntlet MUD - List of banned players
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Util;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

class BanList
{
    protected string $filename;
    protected array $entries = [];

    public function __construct()
    {
        $this->filename = DATA_DIR . 'banned.yaml';
    }

    public function initialize(): void
    {
        Log::info('Reading bans.');

        if (is_readable($this->filename)) {
            $data = file_get_contents($this->filename);

            try {
                $this->entries = Yaml::parse($data);

                if (count($this->entries) > 0) {
                    Log::info(count($this->entries) .  ' banned addresses.');
                }
            } catch (ParseException $ex) {
                Log::error("Unable to parse banfile: " . $ex->getMessage());
            }
        } else {
            Log::warn("Unable to read banfile: " . $this->filename);
        }
    }

    public function list(): array
    {
        return $this->entries;
    }

    public function add(string $ip, string $by): bool
    {
        if (array_key_exists($ip, $this->entries)) {
            return false;
        }

        $this->entries[$ip] = [
            'by' => $by,
            'at' => time()
        ];

        $this->write();
        return true;
    }

    public function remove(string $ip): bool
    {
        if (array_key_exists($ip, $this->entries)) {
            unset($this->entries[$ip]);
            $this->write();
            return true;
        }

        return false;
    }

    public function isBanned(string $ip): bool
    {
        $banned = array_keys($this->entries);
        foreach ($banned as $ban) {
            if (str_starts_with($ip, $ban)) {
                return true;
            }
        }

        return false;
    }

    private function write(): void
    {
        Log::debug('Writing banfile ' . $this->filename . '.');

        $data = Yaml::dump($this->entries);

        $result = @file_put_contents($this->filename, $data);
        if (!$result) {
            Log::error('Unable to write banfile ' . $this->filename . '.');
        }
    }
}
