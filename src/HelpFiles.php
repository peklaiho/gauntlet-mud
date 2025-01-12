<?php
/**
 * Gauntlet MUD - Help files
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Util\Log;
use Gauntlet\Util\MarkdownConverter;

class HelpFiles
{
    protected array $helpFiles;
    protected array $infoFiles;

    public function initialize(): void
    {
        $this->helpFiles = [];
        $this->infoFiles = [];

        Log::info('Reading help files.');

        $files = glob(DATA_DIR . 'help/*');

        foreach ($files as $file) {
            Log::debug("Reading help file $file.");

            $key = basename($file);
            $data = @file_get_contents($file);

            if ($data === false) {
                Log::error("Unable to read help file: $file");
            } else {
                $this->helpFiles[$key] = trim($data);
            }
        }

        Log::info('Reading info files.');

        $files = glob(DATA_DIR . 'info/*');

        foreach ($files as $file) {
            Log::debug("Reading info file $file.");

            $data = @file_get_contents($file);

            if ($data === false) {
                Log::error("Unable to read info file: $file");
            } else {
                $value = MarkdownConverter::convert(trim($data));
                $key = substr($value, 0, strpos($value, "\n"));
                $this->infoFiles[$key] = $value;
            }
        }
    }

    public function getHelp(string $name): ?string
    {
        foreach ($this->helpFiles as $key => $val) {
            if (str_starts_with_case($key, $name)) {
                return $val;
            }
        }

        return null;
    }

    public function getInfo(string $name): array
    {
        $matches = [];

        foreach ($this->infoFiles as $key => $val) {
            if (stripos($key, $name) !== false) {
                $matches[$key] = $val;
            }
        }

        return $matches;
    }

    public function getHelpTopics(): array
    {
        return array_keys($this->helpFiles);
    }

    public function getInfoTopics(): array
    {
        return array_keys($this->infoFiles);
    }
}
