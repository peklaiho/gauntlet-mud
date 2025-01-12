<?php
/**
 * Gauntlet MUD - Bulletin board command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Comm;

use Gauntlet\Act;
use Gauntlet\BulletinBoardEntry;
use Gauntlet\Item;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Data\IBulletinBoardRepository;
use Gauntlet\Enum\AdminLevel;
use Gauntlet\Module\Editor;
use Gauntlet\Util\Config;
use Gauntlet\Util\Currency;
use Gauntlet\Util\GameTime;
use Gauntlet\Util\Input;
use Gauntlet\Util\Log;
use Gauntlet\Util\Preferences;
use Gauntlet\Util\StringSearch;
use Gauntlet\Util\StringValidator;
use Gauntlet\Util\TableFormatter;

class BulletinBoard extends BaseCommand
{
    const FEE = 20;

    public function __construct(
        protected IBulletinBoardRepository $repo,
        protected Editor $editor,
        protected Act $act
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if (!$player->canSeeRoom()) {
            $player->outln(MESSAGE_DARK);
            return;
        }

        $board = $this->findBoard($player);
        if (!$board) {
            $player->outln('There does not appear to be a bulletin board here.');
            return;
        }

        $posts = $board->getTemplate()->getMessages();
        $boardId = $board->getTemplate()->getId();

        // Read new posts
        $this->repo->readInto($boardId, $posts);

        // Handle subcommands
        if ($input->count() == 0 || str_starts_with('list', $input->get(0))) {
            $rows = [];
            foreach ($posts->getAll() as $post) {
                $rows[] = [
                    substr($post->getId(), 0, 4),
                    $post->getAuthor(),
                    $post->getSubject(),
                ];
            }

            if ($rows) {
                $output = TableFormatter::format($rows, [
                    'Id',
                    'Player',
                    'Subject',
                ], [0, 1, 2]);
                foreach ($output as $row) {
                    $player->outln($row);
                }
            } else {
                $player->outln('The bulletin board is empty.');
            }
        } elseif (str_starts_with('read', $input->get(0))) {
            $post = null;

            foreach ($posts->getAll() as $p) {
                if (($input->count() == 1) ||
                    ($input->count() >= 2 && str_starts_with($p->getId(), $input->get(1)))) {
                    $post = $p;
                    break;
                }
            }

            if (!$post) {
                if ($input->count() >= 2) {
                    $player->outln('No post on the bulletin board with given identifier.');
                } else {
                    $player->outln('The bulletin board is empty.');
                }
                return;
            }

            $gt = GameTime::fromUnixTime($post->getCreationTime());
            $player->outln("Post %s by %s on %s of %s, year %d.",
                substr($post->getId(), 0, 4), $post->getAuthor(),
                ordinal_indicator($gt->day()), $gt->monthName(), $gt->year());
            $player->outln('Subject: ' . $post->getSubject());
            $player->outpr($post->getBody(), true);
        } elseif (str_starts_with('delete', $input->get(0)) &&
            AdminLevel::validate(AdminLevel::GreaterGod, $player->getAdminLevel())) {
            if ($input->count() < 2) {
                $player->outln('Delete which post?');
                return;
            }

            foreach ($posts->getAll() as $post) {
                if (str_starts_with($post->getId(), $input->get(1))) {
                    Log::admin("{$player->getName()} deleted post {$post->getId()} on bulletin board $boardId.");
                    $this->repo->delete($boardId, $post->getId());
                    $posts->remove($post);
                    $player->outln("Post {$post->getId()} deleted permanently.");
                    return;
                }
            }

            $player->outln('No post found by given identifier.');
        } elseif (str_starts_with('search', $input->get(0))) {
            $search = $input->getWholeArgSkip(1, true);
            if (!$search) {
                $player->outln('Search for what?');
                return;
            }

            $input = [];
            foreach ($posts->getAll() as $post) {
                $input[$post->getId()] = $post->getSubject() . "\n" . $post->getBody();
            }

            $searchOptions = [];
            $lineWidth = $player->getPreference(Preferences::LINE_LENGTH, 80);
            if ($lineWidth >= 30 + strlen($search)) {
                $searchOptions['exc_len'] = $lineWidth - 10 - strlen($search);
            }

            $matches = StringSearch::search($input, $search, $searchOptions);
            if (!$matches) {
                $player->outln('No posts found by given search query.');
                return;
            }

            foreach ($matches as $match) {
                $player->outln('%s :: %s', substr($match['key'], 0, 4), $player->highlight($match['exc']));
            }
        } elseif (str_starts_with('write', $input->get(0))) {
            $subject = $input->getWholeArgSkip(1, true);
            if (!$subject) {
                $player->outln('Writing a post requires a subject line.');
                return;
            } elseif (!StringValidator::validLettersAndPunctuation($subject)) {
                $player->outln('Subject contains forbidden special characters.');
                return;
            }

            if ($player->getCoins() < self::FEE) {
                $player->outln('You do not have enough ' . Config::moneyType()->value . ' for the posting fee.');
                return;
            }

            $editorOptions = [
                Editor::MAXLEN => 1024,
                Editor::SAVEFN => function ($body) use ($player, $subject, $boardId) {
                    $body = trim($body);
                    if (!$body) {
                        return 'Posting a message requires a message body that is not empty.';
                    } elseif (!StringValidator::validPrintableAscii($body)) {
                        return 'Validation failed. Only printable ASCII characters are allowed.';
                    }

                    $format = Currency::format(self::FEE, false);
                    $player->outln('You pay %s %s for the posting fee. Message posted successfully.',
                        $format, Config::moneyType()->value);
                    $player->addCoins(-self::FEE);

                    Log::info("{$player->getName()} posts new message on bulletin board $boardId.");
                    $this->act->toRoom('@a posts a message on the bulletin board.', true, $player);

                    $postId = bin2hex(random_bytes(4));
                    $time = time();

                    $entry = new BulletinBoardEntry();
                    $entry->setId($postId);
                    $entry->setAuthor($player->getName());
                    $entry->setSubject($subject);
                    $entry->setBody($body);
                    $entry->setCreationTime($time);
                    $entry->setModificationTime($time);

                    $this->repo->write($boardId, $entry);
                    return null;
                }
            ];

            $player->outln("Writing a post with subject '%s'.", $subject);
            $this->act->toRoom('@a starts writing a post for the bulletin board.', true, $player);
            $player->getDescriptor()->setModule($this->editor, $editorOptions);
        } else {
            $player->outln('What do you wish to do with the bulletin board?');
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return 'Interact with a bulletin board (list, read and write posts).';
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            "['list'] [type]",
            "<'read'> [post]",
            "<'search'> <keyword>",
            "<'write'> <subject>",
        ];
    }

    private function findBoard(Player $player): ?Item
    {
        foreach ($player->getRoom()->getItems()->getAll() as $item) {
            if ($item->isBulletinBoard()) {
                return $item;
            }
        }

        return null;
    }
}
