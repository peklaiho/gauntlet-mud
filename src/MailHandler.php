<?php
/**
 * Gauntlet MUD - Mail handler
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Data\IMailRepository;
use Gauntlet\Enum\MailType;
use Gauntlet\Util\Log;

class MailHandler
{
    public function __construct(
        protected IMailRepository $repo,
        protected Lists $lists
    ) {

    }

    public function delete(string $playerName, string $mailId): bool
    {
        return $this->repo->delete($playerName, $mailId);
    }

    public function readPlayerMail(Player $player): int
    {
        return $this->repo->readInto($player->getName(), $player->getMail());
    }

    public function save(string $playerName, MailEntry $mail): bool
    {
        return $this->repo->write($playerName, $mail);
    }

    public function send(string $from, string $to, string $subject, string $body): string
    {
        $id = bin2hex(random_bytes(4));
        $time = time();

        Log::info("New mail $id from $from to $to.");

        $mail = new MailEntry();

        $mail->setId($id);
        $mail->setType(MailType::Sent);
        $mail->setFrom($from);
        $mail->setTo($to);
        $mail->setSubject($subject);
        $mail->setBody($body);
        $mail->setCreationTime($time);
        $mail->setModificationTime($time);

        // Write to sender
        $this->repo->write($from, $mail);

        // Write to recipient
        $mail->setType(MailType::Unread);
        $this->repo->write($to, $mail);

        // Notify recipient if online
        $recipient = $this->lists->findPlayer($to);
        if ($recipient) {
            $recipient->outln('You have new mail.');
        }

        return $id;
    }
}
