<?php
/**
 * Gauntlet MUD - YAML repository for bulletin boards
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Data;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

use Gauntlet\Collection;
use Gauntlet\BulletinBoardEntry;
use Gauntlet\Util\Log;

class YamlBulletinBoardRepository implements IBulletinBoardRepository
{
    protected string $dir;

    public function __construct() {
        $this->dir = DATA_DIR . 'boards/';
    }

    public function readInto(string $boardId, Collection $list): int
    {
        $directory = $this->dir . $boardId;

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

            Log::debug("Reading board file $file.");
            $data = file_get_contents($file);

            try {
                $entry = $this->deserialize(Yaml::parse($data));
                $list->set($entry->getId(), $entry);
                $count++;
            } catch (ParseException $ex) {
                Log::error("Unable to parse board entry in $file: " . $ex->getMessage());
            }
        }

        // Sort: newest first
        if ($count > 0) {
            $list->usort(function (BulletinBoardEntry $a, BulletinBoardEntry $b) {
                return $b->getCreationTime() - $a->getCreationTime();
            });
        }

        return $count;
    }

    public function write(string $boardId, BulletinBoardEntry $entry): bool
    {
        $directory = $this->dir . $boardId;

        // Create directory for board
        if (!file_exists($directory)) {
            Log::debug("Creating board directory $directory");
            $result = @mkdir($directory);
            if (!$result) {
                Log::error("Unable to create board directory $directory.");
                return false;
            }
        }

        $filename = $directory . DIRECTORY_SEPARATOR . $entry->getId() . '.yaml';
        Log::debug("Writing board file $filename");

        $data = $this->serialize($entry);
        $yaml = Yaml::dump($data);

        $result = @file_put_contents($filename, $yaml);
        if (!$result) {
            Log::error("Unable to write board file $filename.");
            return false;
        }

        return true;
    }

    public function delete(string $boardId, string $entryId): bool
    {
        $directory = $this->dir . $boardId;
        $filename = $directory . DIRECTORY_SEPARATOR . $entryId . '.yaml';

        if (!file_exists($filename)) {
            return false;
        }

        Log::debug("Deleting board file $filename");

        $result = @unlink($filename);

        if (!$result) {
            Log::error("Unable to delete board file $filename.");
        }

        return $result;
    }

    private function deserialize(array $data): BulletinBoardEntry
    {
        $entry = new BulletinBoardEntry();

        $entry->setId($data['id']);
        $entry->setAuthor($data['author']);
        $entry->setSubject($data['subject']);
        $entry->setBody($data['body']);
        $entry->setCreationTime($data['created_on']);
        $entry->setModificationTime($data['modified_on']);

        return $entry;
    }

    private function serialize(BulletinBoardEntry $entry): array
    {
        return [
            'id' => $entry->getId(),
            'author' => $entry->getAuthor(),
            'subject' => $entry->getSubject(),
            'body' => $entry->getBody(),
            'created_on' => $entry->getCreationTime(),
            'modified_on' => $entry->getModificationTime(),
        ];
    }
}
