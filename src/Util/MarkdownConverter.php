<?php
/**
 * Gauntlet MUD - Convert Markdown to plain text
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Util;

class MarkdownConverter
{
    public static function convert(string $txt): string
    {
        // Remove headers
        $txt = preg_replace('/#{1,3} /', '', $txt);

        // Remove emphasis
        $txt = preg_replace('/\\*{1,2}([^ ][^\\*]+)\\*{1,2}/', '$1', $txt);

        // Process remote links
        $txt = preg_replace('/\\[([^\\]]+)\\]\\((http[^\\)]+)\\)/', '$1: $2', $txt);

        // Process local links
        $txt = preg_replace('/\\[([^\\]]+)\\]\\(([^\\)]+)\\)/', '{$1}', $txt);

        return $txt;
    }
}
