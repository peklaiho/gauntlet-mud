<?php
/**
 * Gauntlet MUD - Bulletin board entry
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Trait\CreationTime;
use Gauntlet\Trait\ModificationTime;

class BulletinBoardEntry
{
    protected string $id;
    protected string $author;
    protected string $subject;
    protected string $body;

    use CreationTime;
    use ModificationTime;

    public function __construct()
    {

    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setId(string $val): void
    {
        $this->id = $val;
    }

    public function setAuthor(string $val): void
    {
        $this->author = $val;
    }

    public function setSubject(string $val): void
    {
        $this->subject = $val;
    }

    public function setBody(string $val): void
    {
        $this->body = $val;
    }
}
