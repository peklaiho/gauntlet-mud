<?php
/**
 * Gauntlet MUD - Editor module
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Module;

use Gauntlet\Descriptor;
use Gauntlet\Util\Input;
use Gauntlet\Util\Preferences;
use Gauntlet\Util\StringSplitter;

class Editor implements IModule
{
    const CONTENT = 'content';
    const MAXLEN = 'max-len';
    const SAVEFN = 'save-fn';

    public function __construct(
        protected Game $game
    ) {

    }

    public function init(Descriptor $desc): void
    {
        $desc->outln('Type /? for help, /s to save or /q to quit.');

        $content = $desc->getModuleData(self::CONTENT);
        if ($content) {
            if (!$desc->getCompactMode()) {
                $desc->outln();
            }
            foreach ($content as $line) {
                $desc->outln($line);
            }
        }
    }

    public function processInput(Descriptor $desc, Input $input): void
    {
        $raw = $input->getRaw(false);
        $content = $desc->getModuleData(self::CONTENT, []);
        $contentStr = implode("\n", $content);

        if (str_starts_with($raw, '/?')) {
            // Show help
            $desc->outln('Text editor commands:');
            $desc->outln('/?         :: show this help');
            $desc->outln('/.         :: display information about buffer');
            $desc->outln('/a# <text> :: append <text> at end of line #');
            $desc->outln('/c         :: clear buffer (delete all content)');
            $desc->outln('/d#        :: delete line #');
            $desc->outln('/f#        :: format buffer to line-length of #');
            $desc->outln('/fs        :: format each sentence on its own line');
            $desc->outln('/i# <text> :: insert <text> as line #');
            $desc->outln('/l         :: list buffer contents');
            $desc->outln('/ln        :: list buffer contents with line numbers');
            $desc->outln('/p# <text> :: prepend <text> at beginning of line #');
            $desc->outln('/q         :: quit without saving');
            $desc->outln("/r/foo/bar :: replace 'foo' with 'bar'");
            $desc->outln('/s         :: save and exit');
        } elseif (str_starts_with($raw, '/.')) {
            $desc->outln('Lines: ' . count($content));
            $desc->outln('Length: ' . strlen($contentStr));
            $maxLen = $desc->getModuleData(self::MAXLEN);
            if ($maxLen) {
                $desc->outln('Maximum length: ' . $maxLen);
            }
        } elseif (str_starts_with($raw, '/a')) {
            $data = $this->readNumericArgumentAndRest($raw);
            if ($data === null) {
                $desc->outln('Invalid format for append text.');
            } elseif ($data[0] < 1 || $data[0] > count($content)) {
                $desc->outln('Invalid line number.');
            } else {
                $content[$data[0] - 1] = ($content[$data[0] - 1] ?? '') . $data[1];
                $desc->setModuleData(self::CONTENT, $content);
                $desc->outln("Inserted text at end of line {$data[0]}.");
            }
        } elseif (str_starts_with($raw, '/c')) {
            $desc->setModuleData(self::CONTENT, []);
            $desc->outln('Buffer content cleared.');
        } elseif (str_starts_with($raw, '/d')) {
            $idx = $this->readNumericArgument($raw);
            if ($idx === null) {
                $desc->outln('Delete which line?');
            } elseif ($idx < 1 || $idx > count($content)) {
                $desc->outln('Invalid line number.');
            } else {
                array_splice($content, $idx - 1, 1);
                $desc->setModuleData(self::CONTENT, $content);
                $desc->outln("Deleted line $idx.");
            }
        } elseif (str_starts_with($raw, '/f')) {
            if ($this->readCharArgument($raw) == 's') {
                $desc->setModuleData(self::CONTENT, StringSplitter::sentences($contentStr));
                $desc->outln("Buffer formatted according to sentences.");
            } else {
                $len = $this->readNumericArgument($raw);
                if ($len === null) {
                    // Default to 80 first
                    $len = 80;
                    // But try to read value from player preferences
                    if ($desc->getPlayer()) {
                        $playerLineLen = $desc->getPlayer()->getPreference(Preferences::LINE_LENGTH);
                        if ($playerLineLen) {
                            $len = $playerLineLen;
                        }
                    }
                }
                // Reasonable min/max values
                if ($len < 32) {
                    $len = 32;
                } elseif ($len > 256) {
                    $len = 256;
                }
                $desc->setModuleData(self::CONTENT, StringSplitter::paragraph($contentStr, $len));
                $desc->outln("Buffer formatted to lines of $len characters.");
            }
        } elseif (str_starts_with($raw, '/i')) {
            $data = $this->readNumericArgumentAndRest($raw);
            if ($data === null) {
                $desc->outln('Invalid format for insert line.');
            } elseif ($data[0] < 1 || $data[0] > (count($content) + 1)) {
                $desc->outln('Invalid line number.');
            } else {
                array_splice($content, $data[0] - 1, 0, [$data[1]]);
                $desc->setModuleData(self::CONTENT, $content);
                $desc->outln("Inserted new line {$data[0]}.");
            }
        } elseif (str_starts_with($raw, '/l')) {
            $showLineNum = $this->readCharArgument($raw) == 'n';
            foreach ($content as $index => $line) {
                if ($showLineNum) {
                    $desc->out(sprintf('%2d: ', $index + 1));
                }
                $desc->outln($line);
            }
        } elseif (str_starts_with($raw, '/p')) {
            $data = $this->readNumericArgumentAndRest($raw);
            if ($data === null) {
                $desc->outln('Invalid format for prepend text.');
            } elseif ($data[0] < 1 || $data[0] > count($content)) {
                $desc->outln('Invalid line number.');
            } else {
                $content[$data[0] - 1] = $data[1] . ($content[$data[0] - 1] ?? '');
                $desc->setModuleData(self::CONTENT, $content);
                $desc->outln("Inserted text at beginning of line {$data[0]}.");
            }
        } elseif (str_starts_with($raw, '/q')) {
            $desc->setModule($this->game);
        } elseif (str_starts_with($raw, '/r')) {
            if (strlen($raw) > 2) {
                $separator = substr($raw, 2, 1);
                $sepPos = strpos($raw, $separator, 3);
                if ($sepPos !== false) {
                    $search = substr($raw, 3, $sepPos - 3);
                    $replace = substr($raw, $sepPos + 1);
                    $desc->outln("Replace '$search' with '$replace'.");
                    $total = 0;
                    for ($i = 0; $i < count($content); $i++) {
                        $content[$i] = str_replace($search, $replace, $content[$i], $count);
                        $total += $count;
                    }
                    $desc->outln("Replaced $total occurences.");
                    $desc->setModuleData(self::CONTENT, $content);
                    return;
                }
            }
            $desc->outln('Invalid format for replace.');
        } elseif (str_starts_with($raw, '/s')) {
            $fn = $desc->getModuleData(self::SAVEFN);
            $error = $fn($contentStr);
            if ($error) {
                $desc->outln($error);
            } else {
                $desc->setModule($this->game);
            }
        } else {
            if (str_starts_with($raw, '//')) {
                $raw = substr($raw, 1);
            } elseif (str_starts_with($raw, '/')) {
                $desc->outln("Unknown command. Use // to enter a literal '/' character.");
                return;
            }

            $maxLen = $desc->getModuleData(self::MAXLEN);
            if ($maxLen && (strlen($contentStr) + strlen($raw)) > $maxLen) {
                $desc->outln("Maximum length of $maxLen characters exceeded.");
                return;
            }

            // Add this line to content
            $content[] = $raw;
            $desc->setModuleData(self::CONTENT, $content);
        }
    }

    public function prompt(Descriptor $desc): void
    {
        $desc->renderPrompt('EDIT > ');
    }

    private function readCharArgument(string $txt): ?string
    {
        if (preg_match('/^\/[a-z]([a-z]+)/', $txt, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function readNumericArgument(string $txt): ?int
    {
        if (preg_match('/^\/[a-z]([0-9]+)/', $txt, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function readNumericArgumentAndRest(string $txt): ?array
    {
        if (preg_match('/^\/[a-z]([0-9]+) ?(.+)$/', $txt, $matches)) {
            return [
                $matches[1],
                $matches[2]
            ];
        }

        return null;
    }
}
