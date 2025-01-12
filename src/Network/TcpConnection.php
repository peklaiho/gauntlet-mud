<?php
/**
 * Gauntlet MUD - TCP connection
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Network;

class TcpConnection implements IConnection
{
    protected int $readBytes = 0;
    protected int $writeBytes = 0;

    public function __construct(
        protected TcpSocket $socket,
        protected string $ip
    ) {

    }

    public function getAddress(): string
    {
        return $this->ip;
    }

    public function getReadBytes(): int
    {
        return $this->readBytes;
    }

    public function getWriteBytes(): int
    {
        return $this->writeBytes;
    }

    public function close(): void
    {
        @$this->socket->close();
    }

    public function read(): string
    {
        $input = @$this->socket->read();

        if ($input === false) {
            $code = $this->socket->errorCode();

            // non-blocking socket returns SOCKET_EAGAIN
            // if there is no data to read...
            if ($code != SOCKET_EAGAIN) {
                $error = $this->socket->errorText($code);
                throw new NetworkException($error);
            } else {
                $this->socket->clearError();
                return '';
            }
        }

        $this->readBytes += strlen($input);

        return $input;
    }

    public function write(string $data): int
    {
        $written = 0;

        while ($written < strlen($data)) {
            $result = @$this->socket->write(substr($data, $written));

            if ($result === false) {
                $code = $this->socket->errorCode();
                $error = $this->socket->errorText($code);
                throw new NetworkException($error);
            }

            $written += $result;
        }

        $this->writeBytes += $written;

        return $written;
    }
}
