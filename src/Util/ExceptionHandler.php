<?php
/**
 * Gauntlet MUD - Exception handler
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Util;

class ExceptionHandler
{
    public static function handle($e)
    {
        $info = [
            'type' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTrace()
        ];

        Log::crash($e->getMessage(), $info);

        fwrite(STDERR, "{$e->getMessage()} in file {$e->getFile()} line {$e->getLine()}.\n");

        exit(1);
    }
}
