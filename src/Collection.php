<?php
/**
 * Gauntlet MUD - Generic container class that wraps PHP array
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

class Collection
{
    public function __construct(
        protected array $data = []
    ) {

    }

    public function add(mixed $item): void
    {
        $this->data[] = $item;
    }

    public function clear(): void
    {
        $this->data = [];
    }

    public function contains(mixed $item, bool $strict = true): bool
    {
        return array_search($item, $this->data, $strict) !== false;
    }

    public function containsKey(int|string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function empty(): bool
    {
        return count($this->data) == 0;
    }

    public function first(): mixed
    {
        return reset($this->data);
    }

    public function get(int|string $key, mixed $notFoundValue = null): mixed
    {
        return $this->data[$key] ?? $notFoundValue;
    }

    public function getAll(): array
    {
        return $this->data;
    }

    public function remove(mixed $item, bool $strict = true): bool
    {
        $key = array_search($item, $this->data, $strict);

        if ($key === false) {
            return false;
        }

        unset($this->data[$key]);
        return true;
    }

    public function removeKey(int|string $key): void
    {
        unset($this->data[$key]);
    }

    public function set(int|string $key, mixed $item): void
    {
        $this->data[$key] = $item;
    }

    public function sort(bool $byKey = false): void
    {
        if ($byKey) {
            ksort($this->data);
        } else {
            asort($this->data);
        }
    }

    public function usort(callable $fn, bool $byKey = false): void
    {
        if ($byKey) {
            uksort($this->data, $fn);
        } else {
            uasort($this->data, $fn);
        }
    }
}
