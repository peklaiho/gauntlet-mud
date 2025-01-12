<?php
/**
 * Gauntlet MUD - Preferences command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Interface;

use Gauntlet\Player;
use Gauntlet\Enum\AdminLevel;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Module\Editor;
use Gauntlet\Util\ColorPref;
use Gauntlet\Util\Input;
use Gauntlet\Util\Preferences as Prefs;

class Preferences extends BaseCommand
{
    public function __construct(
        protected Prefs $prefs,
        protected Editor $editor
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($input->empty()) {
            foreach ($this->prefs->getList() as $p) {
                if (!AdminLevel::validate($p['admin'] ?? null, $player->getAdminLevel())) {
                    continue;
                }

                $value = $player->getPreferences()->get($p['key']);

                $format = "%-12s   %12s   %-s";
                if ($p['admin'] ?? null) {
                    $format = $player->colorize($format, ColorPref::ADMIN);
                }

                $player->outln($format, $p['key'], $this->displayValue($p, $value), $p['name']);
            }

            return;
        }

        $p = $this->getPrefByKey($input->get(0), $player);
        if (!$p) {
            $player->outln("Unknown preference. Try again.");
            return;
        }

        if ($p['type'] == 'string') {
            $editorOptions = [
                Editor::MAXLEN => $p['max'] ?? 256,
                Editor::SAVEFN => function ($value) use ($player, $p) {
                    $value = trim($value);
                    if (array_key_exists('validate', $p)) {
                        $fn = $p['validate'];
                        if (!$fn($value)) {
                            return $p['name'] . ' validation failed. Check for special characters.';
                        }
                    }
                    $player->getPreferences()->set($p['key'], $value);
                    $player->outln('%s saved successfully.', $p['name']);
                    return null;
                },
            ];

            $content = $player->getPreferences()->get($p['key']);
            if ($content) {
                $editorOptions['content'] = explode("\n", $content);
            }

            $player->outln("Starting editor for '%s'.", $p['name']);
            $player->getDescriptor()->setModule($this->editor, $editorOptions);
            return;
        }

        if ($input->count() > 1) {
            if ($p['type'] == 'bool') {
                if (!strcasecmp($input->get(1), 'ON')) {
                    $value = true;
                } elseif (!strcasecmp($input->get(1), 'OFF')) {
                    $value = false;
                } else {
                    $player->outln("Value for %s should be 'ON' or 'OFF'.", lcfirst($p['name']));
                    return;
                }
            } elseif ($p['type'] == 'integer') {
                $value = intval($input->get(1));
                if ($value < $p['min'] || $value > $p['max']) {
                    $player->outln("Value for %s should be between %d and %d.", lcfirst($p['name']), $p['min'], $p['max']);
                    return;
                }
            } elseif ($p['type'] == 'enum') {
                $value = null;
                foreach ($p['choices'] as $key => $val) {
                    if (str_starts_with_case($val, $input->get(1))) {
                        $value = $key;
                        break;
                    }
                }

                if ($value === null) {
                    $player->outln('Value for %s should be one of:', lcfirst($p['name']));
                    $player->outln('  ' . implode(', ', $p['choices']));
                    return;
                }
            }

            $player->getPreferences()->set($p['key'], $value);
        } else {
            if ($p['type'] == 'enum') {
                $player->outln('This preference requires explicit value which is one of:');
                $player->outln('  ' . implode(', ', $p['choices']));
                return;
            } elseif ($p['type'] != 'bool') {
                $player->outln("This preference requires explicit value and cannot be toggled.");
                return;
            }

            $value = $player->getPreferences()->toggle($p['key']);
        }

        $player->outln("%s has been set to %s.", $p['name'], $this->displayValue($p, $value));
    }

    public function getDescription(?string $subcmd): string
    {
        return "Display your current preferences or toggle a preference on/off. Additionally, some preferences can be given a value. You can also type 'help preferences <key>' to get more information about a specific setting.";
    }

    public function getContextHelp(?string $subcmd): ?array
    {
        return [
            Prefs::BRIEF => "The room description is not shown automatically while moving when Brief mode is enabled. You can still read the room description by using the 'look' command.",
            Prefs::COLOR => "The Color setting controls whether ANSI color sequences are sent by the game to show various elements in specific colors. You can also customize your colors by using the 'colorpref' command.",
            Prefs::COMPACT => "When Compact mode is enabled, the game does not send extra linebreak before prompt. This makes the text more compact.",
            Prefs::DESCRIPTION => "The description of your character. It can be read by other players when they look at you. Please take care that the description fits the theme of the game and does not contain offensive or out-of-character content. Take also into account the equipment your character is wearing (a tattoo on your chest is not visible through a full set of armor). Special characters other than basic punctuation are not allowed. The description is automatically formatted as one paragraph according to the preferences of the reader (manual line-breaks are removed).",
            Prefs::ECHO => "When Echo is enabled, your communications are echoed back to you with their content. Without Echo mode, you will only receive 'Ok.' when sending communications.",
            Prefs::FOLLOW => "When Follow is enabled, you will automatically follow the leader of your party when they move.",
            Prefs::LINE_LENGTH => "If Line-Length is enabled, the game will automatically split longer lines with linebreaks. The value is given as number of characters and 0 (zero) disables the setting, meaning that no linebreaks are inserted by the game. If you are playing with a device that has wide screen, it is recommended to set it to 80 or some sensible value to make reading descriptions easier. If you are playing with a device that has narrow screen, such as a mobile phone, it is recommended to disable this setting by using the value 0.",
            Prefs::PAGE_LENGTH => "If Page-Length is enabled, the game will automatically split long paragraphs into multiple pages and display a prompt between them. The value is given as number of lines and 0 (zero) disables the setting, meaning that long paragraphs are never split into pages. It is recommended to set this value slightly smaller than the total number of visible lines on your screen. Note that Page-Length only works correctly when Line-Length is enabled as well.",
            Prefs::WIMPY => "When Wimpy is enabled, you will automatically attempt to flee from battle when your health drops below the given threshold. The value is given as a percentage of your total health. For example 'wimpy 20' means to flee when your health is at or below 20%.",
        ];
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            '',
            '<key> [value]',
        ];
    }

    public function getSeeAlso(?string $subcmd): array
    {
        return ['colorpref'];
    }

    private function getPrefByKey(string $key, Player $player): ?array
    {
        foreach ($this->prefs->getList() as $p) {
            if (!AdminLevel::validate($p['admin'] ?? null, $player->getAdminLevel())) {
                continue;
            }

            if (str_starts_with_case($p['key'], $key)) {
                return $p;
            }
        }

        return null;
    }

    private function displayValue(array $pref, $value): string
    {
        if ($pref['type'] == 'bool') {
            return $value ? 'ON' : 'OFF';
        } elseif ($pref['type'] == 'integer') {
            return intval($value);
        } elseif ($pref['type'] == 'enum') {
            return $pref['choices'][intval($value)] ?? '(unknown)';
        } elseif ($pref['type'] == 'string') {
            return $value ? 'SET' : 'NOT SET';
        }

        return $value;
    }
}
