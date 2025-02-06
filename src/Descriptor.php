<?php
/**
 * Gauntlet MUD - Player connection instance
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Lists;
use Gauntlet\Module\IModule;
use Gauntlet\Network\IConnection;
use Gauntlet\Network\NetworkException;
use Gauntlet\Network\TelnetHandler;
use Gauntlet\Util\ColorPref;
use Gauntlet\Util\Input;
use Gauntlet\Util\Log;
use Gauntlet\Util\Preferences;
use Gauntlet\Util\StringSplitter;

class Descriptor
{
    private static int $nextId = 1;

    protected int $startTime;
    protected int $id;
    protected ?IModule $module = null;
    protected array $moduleData = [];
    protected ?Player $player = null;
    protected string $inputBuffer = '';
    protected string $outputBuffer = '';
    protected bool $hadInput = false;
    protected ?Input $lastInput = null;
    protected TelnetHandler $telnet;

    public function __construct(
        protected IConnection $conn,
        protected Collection $descriptorList,
        protected Act $act
    ) {
        $this->startTime = time();
        $this->id = self::$nextId++;

        // Index by id
        $this->descriptorList->set($this->id, $this);

        $this->telnet = new TelnetHandler($this);
    }

    public function addPrompt(): void
    {
        // No output or input, return
        if (strlen($this->getOutput()) == 0 && !$this->hadInput) {
            return;
        }

        $this->module->prompt($this);
    }

    public function close(): void
    {
        Log::info("Closing connection #{$this->id}.");

        if ($this->player) {
            Log::info("Setting player {$this->player->getName()} linkless.");
            $this->act->toRoom("@t has disconnected.", true, $this->player);
            $this->player->setDescriptor(null);
        }

        $this->descriptorList->remove($this);

        $this->conn->close();
    }

    public function getAddress(): string
    {
        return $this->conn->getAddress();
    }

    public function getCompactMode(): bool
    {
        if (!$this->getPlayer()) {
            return false;
        }

        return $this->getPlayer()->getPreference(Preferences::COMPACT, false);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLastInput(): ?Input
    {
        return $this->lastInput;
    }

    public function getModule(): IModule
    {
        return $this->module;
    }

    public function getModuleData(string $key, $defaultValue = null)
    {
        return $this->moduleData[$key] ?? $defaultValue;
    }

    public function getOutput(): string
    {
        return $this->outputBuffer;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function getTime(): int
    {
        // The duration of this connection
        return time() - $this->startTime;
    }

    public function getReadBytes(): int
    {
        return $this->conn->getReadBytes();
    }

    public function getWriteBytes(): int
    {
        return $this->conn->getWriteBytes();
    }

    public function out(string $txt): void
    {
        $this->outputBuffer .= $txt;
    }

    public function outln(?string $txt = null): void
    {
        if ($txt) {
            $this->out($txt);
        }

        $this->out("\r\n");
    }

    public function processInput(): void
    {
        $this->hadInput = false;

        if (!$this->readInput()) {
            return;
        }

        $rawInput = StringSplitter::splitInput($this->inputBuffer);

        if ($rawInput) {
            // Store remainder of raw input back (process during next iteration)
            $this->hadInput = true;
            $this->inputBuffer = $rawInput[1];

            // Parse new input (separate arguments)
            $input = new Input($rawInput[0]);

            $this->getModule()->processInput($this, $input);
        }
    }

    public function renderPrompt(string $prompt): void
    {
        if ($this->getPlayer()) {
            $prompt = $this->getPlayer()->colorize($prompt, ColorPref::PROMPT);
        }

        if (!$this->getCompactMode()) {
            $this->outln();
        }

        $this->out($prompt);
    }

    public function setLastInput(Input $input): void
    {
        $this->lastInput = $input;
    }

    public function setModule(IModule $module, array $data = []): void
    {
        $this->module = $module;
        $this->moduleData = $data;
        $module->init($this);
    }

    public function setModuleData(string $key, $val): void
    {
        $this->moduleData[$key] = $val;
    }

    public function setPlayer(?Player $player): void
    {
        $this->player = $player;
    }

    public function writeOutput(): void
    {
        if (empty($this->outputBuffer)) {
            return;
        }

        // Add extra linebreak if we had no input
        if (!$this->hadInput) {
            $this->outputBuffer = "\r\n" . $this->outputBuffer;
        }

        $this->writeRaw($this->outputBuffer);
        $this->outputBuffer = '';
    }

    private function readInput(): bool
    {
        try {
            $input = $this->conn->read();
        } catch (NetworkException $ex) {
            Log::debug("Error while reading from connection #{$this->id}: " . $ex->getMessage());
            $this->close();
            return false;
        }

        list($protocol, $normalInput) = $this->telnet->parseInput($input);

        // Telnet protocol negotiation
        if ($protocol) {
            Log::debug("IN #{$this->id}: " . implode(' ', $protocol));
            $protocolResponse = $this->telnet->makeResponse($protocol);
            if ($protocolResponse) {
                Log::debug("OUT #{$this->id}: " . implode(' ', $protocolResponse));
                if (!$this->writeRaw($protocolResponse)) {
                    return false;
                }
            }
        }

        $this->inputBuffer .= $normalInput;
        return strlen($this->inputBuffer) > 0;
    }

    private function writeRaw(string $data): bool
    {
        try {
            $this->conn->write($data);
            return true;
        } catch (NetworkException $ex) {
            Log::debug("Error while writing to connection #{$this->id}: " . $ex->getMessage());
            $this->close();
            return false;
        }
    }
}
