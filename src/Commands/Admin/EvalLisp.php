<?php
/**
 * Gauntlet MUD - EvalLisp command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Admin;

use Gauntlet\BaseObject;
use Gauntlet\Lists;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Enum\Direction;
use Gauntlet\Util\Input;
use Gauntlet\Util\ItemFinder;
use Gauntlet\Util\LivingFinder;
use Gauntlet\Util\Lisp;

class EvalLisp extends BaseCommand
{
    public const EVAL = 'eval';
    public const EVALAS = 'evalas';

    public function __construct(
        protected ItemFinder $itemFinder,
        protected Lists $lists
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if ($subcmd == self::EVAL) {
            $object = $player;
            $code = $input->getWholeArgument(true);
        } else {
            if ($input->empty()) {
                $player->outln('Eval what as who?');
                return;
            }

            $targetName = strtolower($input->get(0));

            if ($targetName == 'room') {
                $object = $player->getRoom();
            } elseif (str_starts_with($targetName, 'exit')) {
                $parts = explode('-', $targetName);
                if (count($parts) == 2) {
                    $dir = Direction::tryFrom(strtoupper($parts[1]));
                    if ($dir) {
                        $object = $player->getRoom()->getExit($dir);
                    } else {
                        $player->outln('Invalid format for exit. Use exit-N for North and so forth.');
                    }
                } else {
                    $player->outln('Invalid format for exit. Use exit-N for North and so forth.');
                }
            } else {
                $object = $this->findTarget($player, $targetName);
            }

            if (!$object) {
                $player->outln('Nothing and no-one here by that name.');
                return;
            }

            $player->outln('Eval as: ' . $object->getTechnicalName());

            $code = $input->getWholeArgSkip(1, true);
        }

        if (!$code) {
            $player->outln('Eval what?');
            return;
        }

        $value = Lisp::eval($object, $code);
        $result = Lisp::toString($value, true);
        $player->outln('Result: ' . $result);
    }

    public function getDescription(?string $subcmd): string
    {
        if ($subcmd == self::EVAL) {
            return 'Evaluate Lisp code.';
        } else {
            return 'Evaluate Lisp code as another object (room, zone, shop, exit, item, monster or player).';
        }
    }

    public function getUsage(?string $subcmd): array
    {
        if ($subcmd == self::EVAL) {
            return [
                '<code>',
            ];
        } else {
            return [
                "<'room' | 'zone' | 'shop' | exit | item | npc | player> <code>",
            ];
        }
    }

    private function findTarget(Player $player, string $name): ?BaseObject
    {
        $name = strtolower($name);

        // Try static names first
        if ($name == 'room') {
            return $player->getRoom();
        } elseif ($name == 'zone') {
            return $player->getRoom()->getZone();
        } elseif ($name == 'shop') {
            return $this->lists->getShops()->get($player->getRoom()->getId());
        }

        // Try exits next
        $dir = Direction::parseFromName($name);
        if ($dir) {
            $exit = $player->getRoom()->getExit($dir);
            if ($exit) {
                return $exit;
            }
        }

        // Find monster next
        $lists = [$player->getRoom()->getLiving()];
        $target = (new LivingFinder($player, $lists))
            ->find($name);

        if ($target) {
            return $target;
        }

        // Find item last
        $lists = [$player->getRoom()->getItems(), $player->getInventory(), $player->getEquipment()];
        $item = $this->itemFinder->find($player, $name, $lists);

        return $item;
    }
}
