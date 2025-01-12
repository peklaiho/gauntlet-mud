<?php
/**
 * Gauntlet MUD - Script types
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Enum;

enum ScriptType: string
{
    // Executed on player input.
    // Returns truthy value if input was handled and
    // normal command handler should be skipped.
    case Command = 'command';

    // Executed when a monster dies.
    // Returns truthy value to cancel the death.
    case Death = 'death';

    // Executed *after* entering room, when on room.
    // or
    // Executed *before* using an exit, when on exit
    // and returns truthy value if the entry is denied.
    case Entry = 'entry';

    // Executed when monster is fighting.
    // Returns truthy value to skip the default fight action.
    case Fight = 'fight';

    // Executed at initialization.
    case Init = 'init';

    // Executed on update.
    // Returns truthy value to skip the default actions like
    // monster movement and ambient messages.
    case Update = 'update';
}
