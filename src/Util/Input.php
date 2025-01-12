<?php
/**
 * Gauntlet MUD - Class for processing player input
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Util;

use Gauntlet\Collection;

class Input extends Collection
{
    protected string $cmd;

    public function __construct(
        protected string $raw
    ) {
        $parts = StringSplitter::splitBySpace($raw);

        $this->cmd = count($parts) > 0 ? $parts[0] : '';

        parent::__construct(count($parts) > 1 ? array_slice($parts, 1) : []);
    }

    public function getRaw(bool $trim = false): string
    {
        return $trim ? trim($this->raw) : $this->raw;
    }

    public function getCommand(): string
    {
        return $this->cmd;
    }

    public function getWholeArgument(bool $trim = false): string
    {
        if (empty($this->cmd)) {
            return '';
        }

        $whole = substr($this->raw, strpos($this->raw, $this->cmd) + strlen($this->cmd) + 1);

        if ($trim) {
            $whole = trim($whole);
        }

        return $whole;
    }

    public function getWholeArgSkip(int $skip, bool $trim = false): string
    {
        $whole = $this->getWholeArgument(false);

        if ($skip > count($this->data)) {
            return '';
        }

        for ($i = 0; $i < $skip; $i++) {
            $arg = $this->data[$i];
            $whole = substr($whole, strpos($whole, $arg) + strlen($arg) + 1);
        }

        if ($trim) {
            $whole = trim($whole);
        }

        return $whole;
    }

    public function hasFlag(string $flag): bool
    {
        foreach ($this->data as $p) {
            if (strcasecmp($p, "-$flag") == 0) {
                return true;
            }
        }

        return false;
    }

    public function isEmpty(bool $trim = false): bool
    {
        return $this->getRaw($trim) === '';
    }

    public function isInteger(): int|false
    {
        return filter_var($this->getRaw(true), FILTER_VALIDATE_INT);
    }

    public function startsWith(string $start): bool
    {
        return str_starts_with_case($this->getRaw(true), $start);
    }

    public function startsWithAny(array $choices): bool
    {
        foreach ($choices as $start) {
            if ($this->startsWith($start)) {
                return true;
            }
        }

        return false;
    }
}
