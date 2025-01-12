<?php
/**
 * Gauntlet MUD - Main loop
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Module\Intro;
use Gauntlet\Network\INetwork;
use Gauntlet\Util\BanList;
use Gauntlet\Util\Log;
use Gauntlet\Util\Sleeper;

class MainLoop
{
    protected int $startTime;
    protected bool $exit = false;

    public function __construct(
        protected INetwork $network,
        protected World $world,
        protected Intro $intro,
        protected Sleeper $sleeper,
        protected Updater $updater,
        protected Act $act,
        protected BanList $bans,
        protected Socials $socials,
        protected HelpFiles $helpFiles,
        protected Lists $lists,
        protected Fight $fight
    ) {

    }

    public function getStartTime(): int
    {
        return $this->startTime;
    }

    public function run(): void
    {
        // Start counter at 1 so some things
        // are not updated on first iteration.
        $iteration = 1;

        $this->startTime = time();

        Log::info('Starting...');

        $this->world->initialize();
        $this->socials->initialize();
        $this->fight->initialize();
        $this->helpFiles->initialize();
        $this->bans->initialize();
        $this->network->initialize();

        while (!$this->exit) {
            // Record start time
            $this->sleeper->reset();

            // Try to accept new connection
            $conn = $this->network->accept();
            if ($conn) {
                if ($this->bans->isBanned($conn->getAddress())) {
                    Log::warn('Connection from banned address ' . $conn->getAddress() . ', disconnecting.');
                    $conn->close();
                } else {
                    $desc = new Descriptor($conn, $this->lists->getDescriptors(), $this->act);
                    $desc->setModule($this->intro);
                    Log::info('Accepted new connection #' . $desc->getId() . ' from ' . $conn->getAddress() . '.');
                }
            }

            // Read and process inputs
            foreach ($this->lists->getDescriptors()->getAll() as $desc) {
                $desc->processInput();
            }

            // Update everything
            $this->updater->tick($iteration);

            // Write prompts and outputs
            foreach ($this->lists->getDescriptors()->getAll() as $desc) {
                $desc->addPrompt();
                $desc->writeOutput();
            }

            // Sleep and increase counter
            $this->sleeper->sleep($iteration++);
        }

        $this->network->close();

        Log::info('Exiting...');
    }

    public function stop(): void
    {
        $this->exit = true;
    }
}
