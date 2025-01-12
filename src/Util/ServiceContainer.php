<?php
/**
 * Gauntlet MUD - Service container for dependency injection
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Util;

use Closure;
use ReflectionClass;
use RuntimeException;

class ServiceContainer
{
    protected array $init = [];
    protected array $args = [];
    protected array $cache = [];
    protected $debugLog = null;

    /**
     * Return object of the given class from cache if already created.
     */
    public function get(string $id, int $depth = 0): object
    {
        return $this->performGet($id, '', $depth, true);
    }

    /**
     * Return new object of the given class without using cache.
     */
    public function getNew(string $id, int $depth = 0): object
    {
        return $this->performGet($id, '', $depth, false);
    }

    public function set(string $id, mixed $value): void
    {
        $this->init[$id] = $value;
    }

    public function setArg(string $id, string $name, mixed $value): void
    {
        $key = "$id@$name";

        $this->args[$key] = $value;
    }

    public function setArgs(string $id, array $args): void
    {
        foreach ($args as $name => $value) {
            $this->setArg($id, $name, $value);
        }
    }

    public function setDebugLog(callable $log): void
    {
        $this->debugLog = $log;
    }

    private function debug(string $msg, int $depth): void
    {
        if ($this->debugLog) {
            $logFunc = $this->debugLog;
            $logFunc($msg . ' (depth ' . $depth . ')');
        }
    }

    private function performGet(string $id, string $previousId, int $depth, bool $useCache): object
    {
        if ($useCache && array_key_exists($id, $this->cache)) {
            $this->debug("Found $id in cache.", $depth);
            return $this->cache[$id];
        }

        if (array_key_exists($id, $this->init)) {
            $this->debug("Executing init for $id.", $depth);
            $obj = $this->init[$id];
            if ($obj instanceof Closure) {
                $obj = $obj($this, $depth + 1);
            }
            if ($useCache) {
                $this->cache[$id] = $obj;
            }
            return $obj;
        }

        if (!class_exists($id)) {
            throw new RuntimeException("Service container is unable to build class $id for $previousId.");
        } elseif ($depth >= 100) {
            throw new RuntimeException("Recursive loop when trying to build class $id for $previousId.");
        }

        $this->debug("Building $id...", $depth);

        $args = [];

        $rf = new ReflectionClass($id);

        if ($rf->hasMethod('__construct')) {
            $cnst = $rf->getMethod('__construct');
            $params = $cnst->getParameters();

            foreach ($params as $p) {
                $ptype = $p->getType()->getName();
                $pname = $p->getName();

                if ($ptype == self::class) {
                    $this->debug("Injecting container as $pname for $id.", $depth);
                    $args[] = $this;
                } else {
                    $argKey = "$id@$pname";
                    if (array_key_exists($argKey, $this->args)) {
                        $this->debug("Found argument $pname for $id.", $depth);
                        $argValue = $this->args[$argKey];
                        if ($argValue instanceof Closure) {
                            $argValue = $argValue($this);
                        }
                        $args[] = $argValue;
                    } else {
                        $this->debug("Recursively building $ptype for $id.", $depth);
                        $args[] = $this->performGet($ptype, $id, $depth + 1, true);
                    }
                }
            }
        }

        $obj = new $id(...$args);

        if ($useCache) {
            $this->cache[$id] = $obj;
        }

        return $obj;
    }
}
