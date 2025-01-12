<?php
/**
 * Gauntlet MUD - Mail types
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Enum;

enum MailType: string
{
    case Draft = 'draft';
    case Unread = 'unread';
    case Read = 'read';
    case Sent = 'sent';
    case Trash = 'trash';
}
