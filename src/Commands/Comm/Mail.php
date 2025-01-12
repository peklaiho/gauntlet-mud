<?php
/**
 * Gauntlet MUD - Mail command
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Commands\Comm;

use Gauntlet\Act;
use Gauntlet\MailHandler;
use Gauntlet\Player;
use Gauntlet\Commands\BaseCommand;
use Gauntlet\Data\IPlayerRepository;
use Gauntlet\Enum\MailType;
use Gauntlet\Enum\RoomFlag;
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

class Mail extends BaseCommand
{
    const FEE = 20;

    public function __construct(
        protected IPlayerRepository $playerRepo,
        protected MailHandler $mailer,
        protected Editor $editor,
        protected Act $act
    ) {

    }

    public function execute(Player $player, Input $input, ?string $subcmd): void
    {
        if (!$player->canSeeRoom()) {
            $player->outln(MESSAGE_DARK);
            return;
        } elseif (!$player->getRoom()->hasFlag(RoomFlag::Mail)) {
            $player->outln('You must be in a post office to use mail services.');
            return;
        }

        // Read player mail
        $this->mailer->readPlayerMail($player);
        $playerMail = $player->getMail()->getAll();

        // Handle subcommands
        if ($input->count() == 0 || str_starts_with('list', $input->get(0))) {
            $filterType = MailType::Unread;
            $allTypes = false;

            // Filter by given type
            if ($input->count() > 1) {
                if (str_starts_with('all', $input->get(1))) {
                    $allTypes = true;
                } else {
                    $allowedTypes = [
                        MailType::Unread,
                        MailType::Read,
                        MailType::Sent,
                        MailType::Trash,
                    ];

                    $typeFound = false;
                    foreach ($allowedTypes as $type) {
                        if (str_starts_with($type->value, $input->get(1))) {
                            $filterType = $type;
                            $typeFound = true;
                            break;
                        }
                    }

                    if (!$typeFound) {
                        $player->outln('Unknown mail type. Allowed types: all, unread, read, sent, trash');
                        return;
                    }
                }
            }

            $rows = [];
            foreach ($playerMail as $mail) {
                if ($allTypes || $filterType == $mail->getType()) {
                    $rows[] = [
                        substr($mail->getId(), 0, 4),
                        ($mail->getType() == MailType::Sent) ?
                            ('> ' . $mail->getTo()) :
                            ('< ' . $mail->getFrom()),
                        $mail->getSubject(),
                    ];
                }
            }

            if ($rows) {
                $output = TableFormatter::format($rows, [
                    'Id',
                    '  Player',
                    'Subject',
                ], [0, 1, 2]);
                foreach ($output as $row) {
                    $player->outln($row);
                }
            } else {
                if ($allTypes) {
                    $player->outln('No mail.');
                } else {
                    $player->outln("No mail of type '{$filterType->value}'.");
                }
            }
        } elseif (str_starts_with('read', $input->get(0))) {
            $message = null;

            foreach ($playerMail as $mail) {
                if (($input->count() == 1 && $mail->getType() == MailType::Unread) ||
                    ($input->count() >= 2 && str_starts_with($mail->getId(), $input->get(1)))) {
                    $message = $mail;
                    break;
                }
            }

            if (!$message) {
                if ($input->count() >= 2) {
                    $player->outln('No mail found by given identifier.');
                } else {
                    $player->outln('You have no unread mail.');
                }
                return;
            }

            $gt = GameTime::fromUnixTime($mail->getCreationTime());
            $player->outln("Mail %s from %s to %s on %s of %s, year %d.",
                substr($mail->getId(), 0, 4), $mail->getFrom(), $mail->getTo(),
                ordinal_indicator($gt->day()), $gt->monthName(), $gt->year());
            $player->outln('Subject: ' . $mail->getSubject());
            $player->outpr($mail->getBody(), true);

            // Mark message as read
            if ($mail->getType() == MailType::Unread) {
                $mail->setType(MailType::Read);
                $this->mailer->save($player->getName(), $mail);
            }
        } elseif (str_starts_with('delete', $input->get(0))) {
            if ($input->count() < 2) {
                $player->outln('Delete which message?');
                return;
            }

            foreach ($playerMail as $mail) {
                if (str_starts_with($mail->getId(), $input->get(1))) {
                    if ($mail->getType() == MailType::Trash) {
                        $this->mailer->delete($player->getName(), $mail->getId());
                        $player->getMail()->remove($mail);
                        $player->outln('Mail deleted permanently.');
                    } else {
                        $mail->setType(MailType::Trash);
                        $this->mailer->save($player->getName(), $mail);
                        $player->outln('Mail moved to trash. Use delete again to delete permanently.');
                    }
                    return;
                }
            }

            $player->outln('No mail found by given identifier.');
        } elseif (str_starts_with('send', $input->get(0))) {
            if ($input->count() < 2) {
                $player->outln('Send mail to who?');
                return;
            }

            $target = ucfirst($input->get(1));
            if (!$this->playerRepo->has($target)) {
                $player->outln('Unknown recipient.');
                return;
            }

            $subject = $input->getWholeArgSkip(2, true);
            if (!$subject) {
                $player->outln('Sending mail requires a subject line.');
                return;
            } elseif (!StringValidator::validLettersAndPunctuation($subject)) {
                $player->outln('Subject contains forbidden special characters.');
                return;
            }

            if ($player->getCoins() < self::FEE) {
                $player->outln('You do not have enough ' . Config::moneyType()->value . ' for the stamp.');
                return;
            }

            $editorOptions = [
                Editor::MAXLEN => 1024,
                Editor::SAVEFN => function ($body) use ($player, $target, $subject) {
                    $body = trim($body);
                    if (!$body) {
                        return 'Sending mail requires a message body that is not empty.';
                    } elseif (!StringValidator::validPrintableAscii($body)) {
                        return 'Validation failed. Only printable ASCII characters are allowed.';
                    }

                    $format = Currency::format(self::FEE, false);
                    $player->outln('You pay %s %s for stamp. Mail sent successfully.',
                        $format, Config::moneyType()->value);
                    $player->addCoins(-self::FEE);

                    Log::info("{$player->getName()} sends mail to $target.");
                    $this->act->toRoom('@a sends a letter in mail.', true, $player);

                    $this->mailer->send($player->getName(), $target, $subject, $body);
                    return null;
                }
            ];

            $player->outln("Sending mail to %s with subject '%s'.", $target, $subject);
            $this->act->toRoom('@a starts writing a letter.', true, $player);
            $player->getDescriptor()->setModule($this->editor, $editorOptions);
        } elseif (str_starts_with('search', $input->get(0))) {
            $search = $input->getWholeArgSkip(1, true);
            if (!$search) {
                $player->outln('Search for what?');
                return;
            }

            $input = [];
            foreach ($playerMail as $mail) {
                $input[$mail->getId()] = $mail->getSubject() . "\n" . $mail->getBody();
            }

            $searchOptions = [];
            $lineWidth = $player->getPreference(Preferences::LINE_LENGTH, 80);
            if ($lineWidth >= 30 + strlen($search)) {
                $searchOptions['exc_len'] = $lineWidth - 10 - strlen($search);
            }

            $matches = StringSearch::search($input, $search, $searchOptions);
            if (!$matches) {
                $player->outln('No mail found by given search query.');
                return;
            }

            foreach ($matches as $match) {
                $player->outln('%s :: %s', substr($match['key'], 0, 4), $player->highlight($match['exc']));
            }
        } else {
            $player->outln('What do you wish to do with mail?');
        }
    }

    public function getDescription(?string $subcmd): string
    {
        return 'Manage your mail (list, read and send mail to other players).';
    }

    public function getUsage(?string $subcmd): array
    {
        return [
            "['list'] [type]",
            "<'read'> [message]",
            "<'delete'> <message>",
            "<'send'> <player> <subject>",
            "<'search'> <keyword>",
        ];
    }
}
