<?php
/**
 * Gauntlet MUD - Group command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands;

use Gauntlet\Act;
use Gauntlet\GroupHandler;
use Gauntlet\Lists;
use Gauntlet\Living;
use Gauntlet\Player;
use Gauntlet\Util\Input;
use Gauntlet\Util\LivingFinder;
use Gauntlet\Util\TableFormatter;

class GroupCmd extends BaseCommand
{
    public function __construct(
        protected Lists $lists,
        protected GroupHandler $groupHandler,
        protected Act $act
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        $group = $player->getGroup();

        $finder = (new LivingFinder($player, [$this->lists->getLiving()]))
            ->excludeMonsters()
            ->excludeSelf();

        if ($input->empty()) {
            if ($group) {
                $this->showGroup($player);
            } else {
                $player->outln('You are not a member of a party.');
            }
        } elseif (str_starts_with_case('create', $input->get(0))) {
            if ($group) {
                $player->outln('You are already a member of a party.');
                return;
            }

            $group = $this->groupHandler->create($player);

            $this->act->toChar("You have formed a new party. Invite others to join you!", $player);
            $this->act->toRoom("@t has formed a new party.", true, $player);
        } elseif (str_starts_with_case('invite', $input->get(0))) {
            if (!$group || $group->getLeader() !== $player) {
                $player->outln('You are not the leader of a party.');
                return;
            } elseif ($input->count() < 2) {
                // List invitees
                if ($group->getInvitees()->empty()) {
                    $player->outln('No-one has been invited to join your party.');
                } else {
                    $player->outln('Invited players:');
                    foreach ($group->getInvitees()->getAll() as $invitee) {
                        $player->outln('* ' . $invitee);
                    }
                }
                return;
            }

            // Invite new player
            $target = $finder->find($input->get(1));

            if (!$target) {
                $player->outln(MESSAGE_NOONE);
                return;
            } elseif ($target->getGroup()) {
                if ($group === $target->getGroup()) {
                    $this->act->toChar("@E is already a member of your party.", $player, null, $target);
                } else {
                    $this->act->toChar("@E is already a member of another party.", $player, null, $target);
                }
                return;
            } else if ($group->getInvitees()->contains($target->getName())) {
                $this->act->toChar("@E has already been invited to join your party.", $player, null, $target);
                return;
            }

            $group->getInvitees()->add($target->getName());

            $this->act->toChar("You have been invited to join the party of @T.", $target, null, $player);
            $this->act->toList("@t has been invited to join your party.", false, $group->getMembers(), $target);
        } elseif (str_starts_with_case('join', $input->get(0))) {
            if ($group) {
                $player->outln('You are already a member of a party.');
                return;
            } elseif ($input->count() < 2) {
                $player->outln('Join who?');
                return;
            }

            $target = $finder->find($input->get(1));

            if (!$target) {
                $player->outln(MESSAGE_NOONE);
                return;
            }

            $group = $target->getGroup();

            if (!$group) {
                $this->act->toChar("@E is not a member of a party.", $player, null, $target);
                return;
            } elseif (!$group->getInvitees()->contains($player->getName())) {
                $this->act->toChar("You have not been invited to join @S party.", $player, null, $target);
                $this->act->toList("@t attempted to join your party but has not been invited.", false, $group->getMembers(), $player);
                return;
            }

            $this->groupHandler->join($player, $group);
            $group->getInvitees()->remove($player->getName());

            $this->act->toChar("You have joined @S party.", $player, null, $target);
            $this->act->toList("@t has joined the party.", false, $group->getMembers(), $player);
        } elseif (str_starts_with_case('leave', $input->get(0))) {
            if (!$group) {
                $player->outln('You are not a member of a party.');
                return;
            }

            $this->groupHandler->leave($player);

            $this->act->toChar("You have left the party.", $player);
            $this->act->toList("@t has left the party.", false, $group->getMembers(), $player);
        } elseif (str_starts_with_case('promote', $input->get(0))) {
            if (!$group || $group->getLeader() !== $player) {
                $player->outln('You are not the leader of a party.');
                return;
            } elseif ($input->count() < 2) {
                $player->outln('Promote who?');
                return;
            }

            $target = $finder->find($input->get(1));

            if (!$target) {
                $player->outln(MESSAGE_NOONE);
                return;
            } elseif ($group !== $target->getGroup()) {
                $this->act->toChar("@E is not a member of your party.", $player, null, $target);
                return;
            }

            $player->getGroup()->setLeader($target);

            $this->act->toChar("You are now the leader of the party.", $target);
            $this->act->toList("@t is now the leader of the party.", false, $group->getMembers(), $target);
        } elseif (str_starts_with_case('revoke', $input->get(0))) {
            if (!$group || $group->getLeader() !== $player) {
                $player->outln('You are not the leader of a party.');
                return;
            } elseif ($input->count() < 2) {
                $player->outln('Revoke the invitation of who?');
                return;
            }

            foreach ($group->getInvitees()->getAll() as $invitee) {
                if (str_starts_with_case($invitee, $input->get(1))) {
                    $group->getInvitees()->remove($invitee);
                    $this->act->toChar("You have revoked the invitation of @+.", $player, null, $invitee);
                    $this->act->toList("The invitation of @+ to join the party has been revoked.", false, $group->getMembers(), $player);
                    return;
                }
            }

            $player->outln('Your party does not have an invitee with that name.');
        } else {
            $player->outln('Unknown subcommand for party management.');
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return 'Form a party with other players to go on adventures together.';
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            '',
            "<'create'>",
            "<'invite'> [player]",
            "<'join'> <player>",
            "<'leave'>",
            "<'promote'> <player>",
            "<'revoke'> <player>",
        ];
    }

    private function showGroup(Player $player): void
    {
        $group = $player->getGroup();

        $rows = [$this->makeRow($player, $group->getLeader(), true)];

        foreach ($group->getMembers()->getAll() as $member) {
            if ($member === $group->getLeader()) {
                continue;
            }

            $rows[] = $this->makeRow($player, $member);
        }

        $headers = [
            '',
            'Name',
            'Health',
            'Mana',
            'Move',
        ];

        $rows = TableFormatter::format($rows, $headers, [1]);

        foreach ($rows as $row) {
            $player->outln($row);
        }
    }

    private function makeRow(Player $player, Living $member, bool $leader = false): array
    {
        return [
            $leader ? '*' : ' ',
            $player->canSee($member) ? $member->getName() : 'Someone',
            sprintf('%4d / %4d', $member->getHealth(), $member->getMaxHealth()),
            sprintf('%4d / %4d', $member->getMana(), $member->getMaxMana()),
            sprintf('%4d / %4d', $member->getMove(), $member->getMaxMove()),
        ];
    }
}
