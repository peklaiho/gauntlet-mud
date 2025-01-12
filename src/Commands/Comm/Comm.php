<?php
/**
 * Gauntlet MUD - Global communications commands
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Comm;

use Gauntlet\Lists;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Util\ColorPref;
use Gauntlet\Util\Input;
use Gauntlet\Util\Log;
use Gauntlet\Util\Preferences;

class Comm extends BaseCommand
{
    // Note that these subcommand-constants need to
    // have same values as the contants in ColorPref
    // class because they are passed directly to
    // Player::colorize.
    public const ADMIN = 'admin';
    public const GOSSIP = 'gossip';
    public const OOC = 'OOC';

    public function __construct(
        protected Lists $lists
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($input->empty()) {
            $player->outln("What do you wish to $subcmd?");
            return;
        }

        $message = $input->getWholeArgument(true);

        if ($player->getPreference(Preferences::ECHO)) {
            if ($subcmd == self::ADMIN) {
                $format = $player->colorize('You: %s', $subcmd);
            } else {
                $format = $player->colorize("You $subcmd, '%s'", $subcmd);
            }

            $player->outln($format, $message);
        } else {
            $player->outln('Ok.');
        }

        foreach ($this->lists->getLiving()->getAll() as $living) {
            if ($living->isMonster() || $player === $living) {
                continue;
            }
            if ($subcmd == self::ADMIN && !$living->getAdminLevel()) {
                continue;
            }

            if ($subcmd == self::ADMIN) {
                $format = $living->colorize('%s: %s', $subcmd);
            } else {
                $format = $living->colorize("%s {$subcmd}s, '%s'", $subcmd);
            }

            $name = $living->canSee($player) ? $player->getName() : 'Someone';
            $living->outln($format, $name, $message);
        }

        if ($subcmd != self::ADMIN) {
            Log::comm(sprintf("%s %ss, '%s'", $player->getName(), $subcmd, $message));
        }
    }

    public function getDescription(?string $subcmd): string
    {
        if ($subcmd == self::GOSSIP) {
            return 'Speak the given phrase in a voice that is heard by everyone. Use this channel for in-character communication only.';
        } elseif ($subcmd == self::OOC) {
            return 'Speak the given phrase in a voice that is heard by everyone. Use this channel for out-of-character communication.';
        } else {
            return 'Speak the given phrase in a voice that is only heard by administrators.';
        }
    }

    public function getUsage(?string $subcmd): array
    {
        return ['<phrase>'];
    }

    public function getSeeAlso(?string $subcmd): array
    {
        if ($subcmd == self::GOSSIP) {
            return ['ooc', 'say', 'tell'];
        } elseif ($subcmd == self::OOC) {
            return ['gossip', 'say', 'tell'];
        }

        return [];
    }
}
