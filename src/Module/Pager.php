<?php
/**
 * Gauntlet MUD - Pager module
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Module;

use Gauntlet\Descriptor;
use Gauntlet\Util\Input;
use Gauntlet\Util\Preferences;

class Pager implements IModule
{
    public function __construct(
        protected Game $game
    ) {

    }

    public function init(Descriptor $desc): void
    {
        $desc->setModuleData('current', 1);
        $this->showPage($desc, 1);
    }

    public function processInput(Descriptor $desc, Input $input): void
    {
        $pages = $desc->getModuleData('pages');
        $current = $desc->getModuleData('current');

        $intval = $input->isInteger();

        if ($intval !== false) {
            $current = $intval;
        } elseif ($input->isEmpty(true) || $input->startsWith('n')) {
            $current++;
        } elseif ($input->startsWith('p')) {
            $current--;
        } elseif ($input->startsWith('c')) {
            // show current page again (do nothing)
        } elseif ($input->startsWith('q')) {
            $desc->setModule($this->game);
            return;
        } elseif ($input->startsWithAny(['?', 'h'])) {
            // show help
            $desc->outln('The following commands are available in pager:');
            $desc->outln('  ? or h        - show this help');
            $desc->outln('  p             - previous page');
            $desc->outln('  n or <enter>  - next page');
            $desc->outln('  1 .. n        - show given page');
            $desc->outln('  c             - show current page');
            $desc->outln('  q             - quit');
            return;
        } else {
            $desc->outln('Unknown command. Type ? for help.');
            return;
        }

        // Check range
        $current = min(count($pages), max(1, $current));

        // Show the chosen page
        $this->showPage($desc, $current);

        // Quit if last page
        if ($current == count($pages)) {
            $desc->setModule($this->game);
        } else {
            $desc->setModuleData('current', $current);
        }
    }

    public function prompt(Descriptor $desc): void
    {
        $prompt = sprintf('[ page %d of %d (q to quit, ? for help) ] ',
            $desc->getModuleData('current'), count($desc->getModuleData('pages')));

        $desc->renderPrompt($prompt);
    }

    private function showPage(Descriptor $desc, int $num): void
    {
        $pages = $desc->getModuleData('pages');

        foreach ($pages[$num - 1] as $line) {
            $desc->outln($line);
        }
    }
}
