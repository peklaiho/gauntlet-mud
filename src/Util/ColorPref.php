<?php
/**
 * Gauntlet MUD - Color preferences
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Util;

class ColorPref
{
    const HIGHLIGHT      = 'highlight';
    const PROMPT         = 'prompt';

    const ROOMNAME       = 'roomname';
    const ROOMDESC       = 'roomdesc';
    const ROOMEXIT       = 'roomexit';
    const ROOMNPC        = 'roomnpc';
    const ROOMOBJ        = 'roomobj';
    const ROOMPLAYER     = 'roomplayer';

    const SYSLOG         = 'syslog';
    const ADMIN          = 'admin';
    const SAY            = 'say';
    const TELL           = 'tell';
    const GOSSIP         = 'gossip';
    const OOC            = 'OOC';

    protected array $values;

    public function __construct()
    {
        $this->setDefaults();
    }

    public function get(string $key): string
    {
        return $this->values[$key] ?? 'n';
    }

    public function getAll(): array
    {
        return $this->values;
    }

    public function set(string $key, string $val): void
    {
        $this->values[$key] = $val;
    }

    public function setAll(array $values): void
    {
        // Do not replace whole array so we keep defaults
        foreach ($values as $key => $val) {
            $this->values[$key] = $val;
        }
    }

    public function setDefaults()
    {
        $this->values = [
            self::HIGHLIGHT    => Color::YELLOW,

            self::ROOMNAME     => Color::GREEN,
            self::ROOMDESC     => Color::RESET,
            self::ROOMEXIT     => Color::YELLOW,
            self::ROOMNPC      => Color::MAGENTA,
            self::ROOMOBJ      => Color::CYAN,
            self::ROOMPLAYER   => Color::RED,

            self::SYSLOG       => Color::GREEN,
            self::ADMIN        => Color::CYAN,
            self::SAY          => Color::RESET,
            self::TELL         => Color::RESET,
            self::GOSSIP       => Color::YELLOW,
            self::OOC          => Color::BLUE,
        ];
    }
}
