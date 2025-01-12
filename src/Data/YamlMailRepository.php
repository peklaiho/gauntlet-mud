<?php
/**
 * Gauntlet MUD - YAML repository for mail
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Data;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

use Gauntlet\Collection;
use Gauntlet\MailEntry;
use Gauntlet\Enum\MailType;
use Gauntlet\Util\Log;

class YamlMailRepository implements IMailRepository
{
    protected string $dir;

    public function __construct() {
        $this->dir = DATA_DIR . 'mail/';
    }

    public function readInto(string $player, Collection $list): int
    {
        $directory = $this->dir . $player;

        if (!is_readable($directory)) {
            return 0;
        }

        $files = glob($directory . '/*.yaml');

        $count = 0;

        foreach ($files as $file) {
            // Check if this file is already in collection
            $info = pathinfo($file);
            if ($list->containsKey($info['filename'])) {
                continue;
            }

            Log::debug("Reading mail file $file.");
            $data = file_get_contents($file);

            try {
                $mail = $this->deserialize(Yaml::parse($data));
                $list->set($mail->getId(), $mail);
                $count++;
            } catch (ParseException $ex) {
                Log::error("Unable to parse mail in $file: " . $ex->getMessage());
            }
        }

        // Sort: newest first
        if ($count > 0) {
            $list->usort(function (MailEntry $a, MailEntry $b) {
                return $b->getCreationTime() - $a->getCreationTime();
            });
        }

        return $count;
    }

    public function write(string $player, MailEntry $mail): bool
    {
        $directory = $this->dir . $player;

        // Create directory for player
        if (!file_exists($directory)) {
            Log::debug("Creating mail directory $directory");
            $result = @mkdir($directory);
            if (!$result) {
                Log::error("Unable to create mail directory $directory.");
                return false;
            }
        }

        $filename = $directory . DIRECTORY_SEPARATOR . $mail->getId() . '.yaml';
        Log::debug("Writing mail file $filename");

        $data = $this->serialize($mail);
        $yaml = Yaml::dump($data);

        $result = @file_put_contents($filename, $yaml);
        if (!$result) {
            Log::error("Unable to write mail file $filename.");
            return false;
        }

        return true;
    }

    public function delete(string $player, string $id): bool
    {
        $directory = $this->dir . $player;
        $filename = $directory . DIRECTORY_SEPARATOR . $id . '.yaml';

        if (!file_exists($filename)) {
            return false;
        }

        Log::debug("Deleting mail file $filename");

        $result = @unlink($filename);

        if (!$result) {
            Log::error("Unable to delete mail file $filename.");
        }

        return $result;
    }

    private function deserialize(array $data): MailEntry
    {
        $mail = new MailEntry();

        $mail->setId($data['id']);
        $mail->setType(MailType::tryFrom($data['type']) ?? MailType::Unread);
        $mail->setFrom($data['from']);
        $mail->setTo($data['to']);
        $mail->setSubject($data['subject']);
        $mail->setBody($data['body']);
        $mail->setCreationTime($data['created_on']);
        $mail->setModificationTime($data['modified_on']);

        return $mail;
    }

    private function serialize(MailEntry $mail): array
    {
        return [
            'id' => $mail->getId(),
            'type' => $mail->getType()->value,
            'from' => $mail->getFrom(),
            'to' => $mail->getTo(),
            'subject' => $mail->getSubject(),
            'body' => $mail->getBody(),
            'created_on' => $mail->getCreationTime(),
            'modified_on' => $mail->getModificationTime(),
        ];
    }
}
