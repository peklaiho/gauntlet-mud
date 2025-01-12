<?php
/**
 * Gauntlet MUD - Player preferences
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Util;

use Gauntlet\Enum\AdminLevel;

class Preferences
{
    const BRIEF = 'brief';
    const COLOR = 'color';
    const COMPACT = 'compact';
    const DESCRIPTION = 'description';
    const ECHO = 'echo';
    const FOLLOW = 'follow';
    const LINE_LENGTH = 'line-length';
    const PAGE_LENGTH = 'page-length';
    const WIMPY = 'wimpy';

    const PERMLIGHT = 'permlight';
    const SYSLOG = 'syslog';

    protected array $values = [];

    private static array $list = [
        [
            'key' => self::BRIEF,
            'name' => 'Brief mode',
            'type' => 'bool'
        ],
        [
            'key' => self::COLOR,
            'name' => 'Color output',
            'type' => 'bool'
        ],
        [
            'key' => self::COMPACT,
            'name' => 'Compact mode',
            'type' => 'bool'
        ],
        [
            'key' => self::DESCRIPTION,
            'name' => 'Character description',
            'type' => 'string',
            'max' => 768,
            'validate' => [StringValidator::class, 'validLettersAndPunctuation']
        ],
        [
            'key' => self::ECHO,
            'name' => 'Echo communications',
            'type' => 'bool'
        ],
        [
            'key' => self::FOLLOW,
            'name' => 'Follow party leader',
            'type' => 'bool'
        ],
        [
            'key' => self::LINE_LENGTH,
            'name' => 'Line length',
            'type' => 'integer',
            'min' => 0,
            'max' => 200
        ],
        [
            'key' => self::PAGE_LENGTH,
            'name' => 'Page length',
            'type' => 'integer',
            'min' => 0,
            'max' => 200
        ],
        [
            'key' => self::WIMPY,
            'name' => 'Wimpy (auto flee) percent',
            'type' => 'integer',
            'min' => 0,
            'max' => 100
        ],

        // Admin preferences
        [
            'key' => self::PERMLIGHT,
            'name' => 'Permanent light',
            'type' => 'bool',
            'admin' => AdminLevel::Immortal
        ],
        [
            'key' => self::SYSLOG,
            'name' => 'Syslog level',
            'type' => 'enum',
            'choices' => [
                0 => 'OFF',
                Log::DEBUG => 'DEBUG',
                Log::ADMIN => 'ADMIN',
                Log::INFO => 'INFO',
                Log::WARN => 'WARN',
                Log::ERROR => 'ERROR'
            ],
            'admin' => AdminLevel::GreaterGod
        ],
    ];

    public function get(string $key, $defaultValue = null)
    {
        return $this->values[$key] ?? $defaultValue;
    }

    public function getAll(): array
    {
        return $this->values;
    }

    public function getList(): array
    {
        return self::$list;
    }

    public function set(string $key, $value): void
    {
        $this->values[$key] = $value;
    }

    public function setAll(array $prefs): void
    {
        $this->values = $prefs;
    }

    public function toggle(string $key)
    {
        $newValue = !$this->get($key);
        $this->set($key, $newValue);
        return $newValue;
    }
}
