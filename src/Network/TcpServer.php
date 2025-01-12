<?php
/**
 * Gauntlet MUD - TCP server
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Network;

use Socket;

use Gauntlet\Util\Log;

class TcpServer implements INetwork
{
    protected Socket $socket;

    public function initialize(): void
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        if (!$this->socket) {
            throw new NetworkException('Unable to create TCP socket.');
        }

        if (!socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1)) {
            throw new NetworkException('Unable to set REUSEADDR option for TCP socket.');
        }

        if (!socket_bind($this->socket, '0.0.0.0', NETWORK_PORT)) {
            throw new NetworkException('Unable to bind TCP socket to port ' . NETWORK_PORT . '.');
        }

        if (!socket_listen($this->socket)) {
            throw new NetworkException('Unable to set TCP socket to listen.');
        }

        if (!socket_set_nonblock($this->socket)) {
            throw new NetworkException('Unable to set TCP socket to non-blocking mode.');
        }

        Log::info('Opened TCP socket, listening on port ' . NETWORK_PORT . '.');
    }

    public function accept(): ?IConnection
    {
        $sock = socket_accept($this->socket);

        if (!$sock) {
            return null;
        }

        Log::info("Incoming connection.");

        // Set to non-blocking mode
        if (!socket_set_nonblock($sock)) {
            $errorCode = socket_last_error($sock);
            $errorText = socket_strerror($errorCode);
            Log::warn('Error setting client socket to non-blocking: ' . $errorText);

            @socket_close($sock);
            return null;
        }

        if (!socket_getpeername($sock, $ip)) {
            $errorCode = socket_last_error($sock);
            $errorText = socket_strerror($errorCode);
            Log::warn('Error reading client IP address: ' . $errorText);

            @socket_close($sock);
            return null;
        }

        $sockWrapper = new TcpSocket($sock);
        return new TcpConnection($sockWrapper, $ip);
    }

    public function close(): void
    {
        Log::info('Closing mother socket.');
        socket_close($this->socket);
    }
}
