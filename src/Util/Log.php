<?php
/**
 * Gauntlet MUD - Logging functions
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Util;

use Gauntlet\Lists;
use Gauntlet\Player;
use Gauntlet\Enum\AdminLevel;

class Log
{
    // Log levels
    const DEBUG = 100;
    const ADMIN = 150;
    const INFO = 200;
    const WARN = 300;
    const ERROR = 400;

    private static Lists $lists;

    public static function initialize(Lists $lists): void
    {
        self::$lists = $lists;
    }

    // Add a log entry using level that is given as argument
    public static function add(string $level, string $message, array $context = []): void
    {
        self::$level($message, $context);
    }

    public static function admin(string $message, array $context = []): void
    {
        self::write('admin.log', $message, $context);
        self::announce(self::ADMIN, $message, $context);
    }

    public static function comm(string $message, array $context = []): void
    {
        self::write('comm.log', $message, $context);
    }

    public static function debug(string $message, array $context = []): void
    {
        self::write('debug.log', $message, $context);
        self::announce(self::DEBUG, $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::write('main.log', "INFO: $message", $context);
        self::announce(self::INFO, $message, $context);
    }

    public static function warn(string $message, array $context = []): void
    {
        self::write('main.log', "WARN: $message", $context);
        self::announce(self::WARN, $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('main.log', "ERROR: $message", $context);
        self::announce(self::ERROR, $message, $context);
    }

    public static function crash(string $message, array $context = []): void
    {
        self::write('crash.log', $message, $context);
    }

    public static function money(string $message, array $context = []): void
    {
        self::write('money.log', $message, $context);
    }

    private static function write(string $file, string $message, array $context = []): void
    {
        // Full filename includes the current month
        $month = date('Y-m');
        $file = DATA_DIR . 'logs' . DIRECTORY_SEPARATOR . $month . DIRECTORY_SEPARATOR . $file;

        // Create directory if it does not exist
        $dir = dirname($file);
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0755, true)) {
                error_log('Unable to create log directory: ' . $dir);
                exit(1);
            }
        }

        $now = (new \DateTime())->format('Y-m-d H:i:s.v');

        $message = "[$now] $message";
        if ($context) {
            $message .= ' ' . json_encode($context);
        }

        if (!file_put_contents($file, $message . "\n", FILE_APPEND)) {
            error_log('Unable to write logfile: ' . $file);
            exit(1);
        }
    }

    private static function announce(int $level, string $message, array $context = []): void
    {
        $start = [
            self::DEBUG => 'DEBUG: ',
            self::ADMIN => 'ADMIN: ',
            self::INFO => 'INFO: ',
            self::WARN => 'WARN: ',
            self::ERROR => 'ERROR: ',
        ];

        $message = $start[$level] . $message;

        if ($context) {
            $message .= ' ' . json_encode($context);
        }

        foreach (self::$lists->getLiving()->getAll() as $living) {
            if ($living->isMonster() || !AdminLevel::validate(AdminLevel::GreaterGod, $living->getAdminLevel())) {
                continue;
            }

            $prefLevel = $living->getPreference(Preferences::SYSLOG, 0);
            if ($prefLevel == 0 || $prefLevel > $level) {
                continue;
            }

            if ($level == self::ERROR) {
                $living->outln($living->colorizeStatic($message, Color::BRED));
            } else {
                $living->outln($living->colorize($message, ColorPref::SYSLOG));
            }
        }
    }
}
