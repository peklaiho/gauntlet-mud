<?php
/**
 * Gauntlet MUD - Game module
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Module;

use Gauntlet\CommandParser;
use Gauntlet\Descriptor;
use Gauntlet\Player;
use Gauntlet\Socials;
use Gauntlet\Enum\ScriptType;
use Gauntlet\Util\Input;
use Gauntlet\Util\Lisp;
use Gauntlet\Util\Log;
use Gauntlet\Util\Preferences;

class Game implements IModule
{
    public function __construct(
        protected CommandParser $parser,
        protected Socials $socials
    ) {

    }

    public function init(Descriptor $desc): void
    {

    }

    public function processInput(Descriptor $desc, Input $input): void
    {
        // If we don't have actual input, e.g. the user just pressed enter,
        // don't do anything (the prompt is still displayed).
        if ($input->getCommand() == '') {
            return;
        }

        // Repeat previous command if requested (and ignore new input)
        if ($input->getCommand() == '!' && $desc->getLastInput()) {
            $input = $this->combineInput($input, $desc->getLastInput());
        }

        // Save last input
        $desc->setLastInput($input);

        // Handle aliases
        $aliases = $desc->getPlayer()->getAliases();
        foreach ($aliases as $name => $value) {
            // Require exact match
            if (strcasecmp($input->getCommand(), $name) == 0) {
                // Replace arguments
                $value = str_replace('$*', $input->getWholeArgument(true), $value);
                for ($i = 1; $i <= 9; $i++) {
                    $value = str_replace('$' . $i, $input->get($i - 1, ''), $value);
                }

                // Make new input
                $input = new Input($value);
                break;
            }
        }

        // Execute script command
        if ($this->runScriptCommand($desc->getPlayer(), $input)) {
            return;
        }

        // Parse and execute command
        if ($this->parser->parse($desc->getPlayer(), $input)) {
            return;
        }

        // Try socials next
        if ($this->socials->parse($desc->getPlayer(), $input)) {
            return;
        }

        // Nothing found, try to find a suggestion
        $suggestion = $this->parser->suggestion($desc->getPlayer(), $input);
        if ($suggestion) {
            $desc->outln("Unknown command, did you mean '$suggestion'?");
        } else {
            $desc->outln("Unknown command, type 'commands' for list.");
        }
    }

    public function prompt(Descriptor $desc): void
    {
        $prompt = sprintf('[ %.0fH %.0fM %.0fV ] ',
            $desc->getPlayer()->getHealth(),
            $desc->getPlayer()->getMana(),
            $desc->getPlayer()->getMove());

        $desc->renderPrompt($prompt);
    }

    private function combineInput(Input $new, Input $old): Input
    {
        if ($new->empty()) {
            // No arguments, just run the old input as is
            return $old;
        }

        // Else combine arguments from both commands
        $parts = [$old->getCommand()];

        // If we have more or equal args, we use all from new
        if ($new->count() >= $old->count()) {
            $parts = array_merge($parts, $new->getAll());
        } else {
            $diff = $old->count() - $new->count();

            for ($i = 0; $i < $old->count(); $i++) {
                if ($i < $diff) {
                    $parts[] = $old->get($i);
                } else {
                    $parts[] = $new->get($i - $diff);
                }
            }
        }

        return new Input(implode(' ', $parts));
    }

    private function runScriptCommand(Player $player, Input $input): bool
    {
        $script = $this->findScriptCommand($player);

        if (!$script) {
            return false;
        }

        $data = [
            'raw-input' => $input->getRaw(),
            'command' => $input->getCommand(),
            'arguments' => $input->getAll(),
        ];

        $result = Lisp::evalWithData($player, $script, $data);

        return boolval($result);
    }

    private function findScriptCommand(Player $player): ?string
    {
        // Room monsters
        foreach ($player->getRoom()->getLiving()->getAll() as $obj) {
            if ($obj->isPlayer() || !$player->canSee($obj)) {
                continue;
            }

            $script = $obj->getScript(ScriptType::Command);
            if ($script) {
                return $script;
            }
        }

        // Room items
        foreach ($player->getRoom()->getItems()->getAll() as $obj) {
            if (!$player->canSeeItem($obj)) {
                continue;
            }

            $script = $obj->getScript(ScriptType::Command);
            if ($script) {
                return $script;
            }
        }

        // Room itself
        if ($player->canSeeRoom()) {
            $script = $player->getRoom()->getScript(ScriptType::Command);
            if ($script) {
                return $script;
            }
        }

        // Carried items
        foreach ($player->getInventory()->getAll() as $obj) {
            if (!$player->canSeeItem($obj)) {
                continue;
            }

            $script = $obj->getScript(ScriptType::Command);
            if ($script) {
                return $script;
            }
        }

        // Worn items
        foreach ($player->getEquipment()->getAll() as $obj) {
            if (!$player->canSeeItem($obj)) {
                continue;
            }

            $script = $obj->getScript(ScriptType::Command);
            if ($script) {
                return $script;
            }
        }

        return null;
    }
}
