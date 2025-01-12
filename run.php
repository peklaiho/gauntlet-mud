<?php
/**
 * Gauntlet MUD - Main file for running the game
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

require(__DIR__ . '/bootstrap.php');

// Process command line arguments

if ($argc < 3) {
    echo "Usage: {$argv[0]} <port> <data-dir>\n";
    exit(1);
}

$port = $argv[1];
if (!filter_var($port, FILTER_VALIDATE_INT) || $port < 1024 || $port > 65535) {
    echo "Invalid port number.\n";
    exit(1);
}

$dataDir = realpath($argv[2]);
if (!$dataDir || !is_readable($dataDir) || !is_dir($dataDir)) {
    echo "Invalid data directory or file permissions.\n";
    exit(1);
}

// Add trailing slash if missing
if (!str_ends_with($dataDir, DIRECTORY_SEPARATOR)) {
    $dataDir .= DIRECTORY_SEPARATOR;
}

// Define constants
define('APP_DIR', __DIR__ . DIRECTORY_SEPARATOR);
define('DATA_DIR', $dataDir);
define('NETWORK_PORT', $port);

// Initialize static entities
Gauntlet\Util\Log::initialize(SERVICE_CONTAINER->get(Gauntlet\Lists::class));
Gauntlet\Util\Lisp::initialize(SERVICE_CONTAINER->get(Gauntlet\LispFuncs::class));

// Define the exception handler
set_exception_handler(['Gauntlet\Util\ExceptionHandler', 'handle']);

// Run the main loop
$mainLoop = SERVICE_CONTAINER->get(Gauntlet\MainLoop::class);
$mainLoop->run();
