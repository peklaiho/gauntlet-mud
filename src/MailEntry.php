<?php
/**
 * Gauntlet MUD - Mail entry
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Enum\MailType;
use Gauntlet\Trait\CreationTime;
use Gauntlet\Trait\ModificationTime;

class MailEntry
{
    protected string $id;
    protected MailType $type;
    protected string $from;
    protected string $to;
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

    public function getType(): MailType
    {
        return $this->type;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function getTo(): string
    {
        return $this->to;
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

    public function setType(MailType $val): void
    {
        $this->type = $val;
    }

    public function setFrom(string $val): void
    {
        $this->from = $val;
    }

    public function setTo(string $val): void
    {
        $this->to = $val;
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
