<?php
/**
 * Gauntlet MUD - TCP socket
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Network;

use Socket;

// Simple wrapper for PHP's socket_ functions.
class TcpSocket
{
    public function __construct(
        protected Socket $rawSocket
    ) {

    }

    public function read(): string|false
    {
        return socket_read($this->rawSocket, 1024);
    }

    public function write(string $data): int|false
    {
        return socket_write($this->rawSocket, $data);
    }

    public function close(): void
    {
        socket_close($this->rawSocket);
    }

    public function clearError(): void
    {
        socket_clear_error($this->rawSocket);
    }

    public function errorCode(): int
    {
        return socket_last_error($this->rawSocket);
    }

    public function errorText(int $code): string
    {
        return socket_strerror($code);
    }
}
