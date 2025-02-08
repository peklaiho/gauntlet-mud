<?php
/**
 * Gauntlet MUD - Intro module
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet\Module;

use RuntimeException;

use Gauntlet\Act;
use Gauntlet\Descriptor;
use Gauntlet\Lists;
use Gauntlet\MailHandler;
use Gauntlet\Player;
use Gauntlet\PlayerItems;
use Gauntlet\Renderer;
use Gauntlet\World;
use Gauntlet\Data\IPlayerRepository;
use Gauntlet\Enum\EqSlot;
use Gauntlet\Enum\MailType;
use Gauntlet\Enum\PlayerClass;
use Gauntlet\Enum\Sex;
use Gauntlet\Enum\Size;
use Gauntlet\Util\Config;
use Gauntlet\Util\Input;
use Gauntlet\Util\Log;
use Gauntlet\Util\StringValidator;

class Intro implements IModule
{
    const STATE_ASK_NAME = 0;
    const STATE_ASK_PASSWORD = 1;

    const STATE_CONFIRM_NAME = 10;
    const STATE_NEW_PASSWORD = 11;
    const STATE_CONFIRM_PW = 12;
    const STATE_SELECT_SEX = 13;
    const STATE_SELECT_CLASS = 14;
    const STATE_SELECT_SIZE = 15;
    const STATE_SELECT_DEVICE = 16;

    public function __construct(
        protected IPlayerRepository $playerRepo,
        protected PlayerItems $playerItems,
        protected MailHandler $mailHandler,
        protected Renderer $render,
        protected World $world,
        protected Game $game,
        protected Lists $lists,
        protected Act $act
    ) {

    }

    public function init(Descriptor $desc): void
    {
        $desc->outln('Welcome to ' . Config::gameName() . '.');
        $desc->outln();
        $desc->out("What is your name? ");
        $desc->setModuleData('state', self::STATE_ASK_NAME);
    }

    public function processInput(Descriptor $desc, Input $input): void
    {
        $raw = $input->getRaw(true);

        switch ($desc->getModuleData('state')) {
            case self::STATE_ASK_NAME:
                if (empty($raw)) {
                    $desc->close();
                    return;
                }

                $name = ucfirst($raw);

                if (!StringValidator::validPlayerName($name)) {
                    $desc->outln("Invalid name, try again.");
                    $desc->out("What is your name? ");
                } else {
                    $player = $this->playerRepo->findByName($name);

                    if ($player) {
                        $desc->setModuleData('player', $player);
                        $desc->outln("Character by that name already exists.");
                        $desc->out("Password: ");
                        $desc->setModuleData('state', self::STATE_ASK_PASSWORD);
                    } else {
                        $desc->setModuleData('name', $name);
                        $desc->outln("Creating new character: $name");
                        $desc->outln('Please note that character name must be appropriate for a ' .
                            Config::gameType()->value . ' setting.');
                        $desc->out("Are you sure you wish to be known as $name? ");
                        $desc->setModuleData('state', self::STATE_CONFIRM_NAME);
                    }
                }
                break;

            case self::STATE_ASK_PASSWORD:
                if (empty($raw)) {
                    $desc->out("What is your name then? ");
                    $desc->setModuleData('state', self::STATE_ASK_NAME);
                } elseif (password_verify($raw, $desc->getModuleData('player')->getPassword())) {
                    $this->enterGame($desc, false, false);
                } else {
                    $fails = $desc->getModuleData('fails', 0) + 1;
                    if ($fails >= 3) {
                        // Disconnect after 3 failed attempts
                        Log::warn(sprintf("%d failed login attempts for %s from connection #%d (%s).",
                            $fails, $desc->getModuleData('player')->getName(),
                            $desc->getId(), $desc->getAddress()));
                        $desc->close();
                        return;
                    } else {
                        $desc->setModuleData('fails', $fails);
                        $desc->out("Invalid password, try again. Password: ");
                    }
                }
                break;

            case self::STATE_CONFIRM_NAME:
                if (str_starts_with_case($raw, 'y')) {
                    $desc->out("Enter new password: ");
                    $desc->setModuleData('state', self::STATE_NEW_PASSWORD);
                } else {
                    $desc->out("What is your name then? ");
                    $desc->setModuleData('state', self::STATE_ASK_NAME);
                }
                break;

            case self::STATE_NEW_PASSWORD:
                if (!StringValidator::validPassword($raw)) {
                    $desc->out("Invalid password, try again. New password: ");
                } else {
                    $desc->setModuleData('password', $raw);
                    $desc->out("Repeat password: ");
                    $desc->setModuleData('state', self::STATE_CONFIRM_PW);
                }
                break;

            case self::STATE_CONFIRM_PW:
                if ($raw !== $desc->getModuleData('password')) {
                    $desc->out("Passwords do not match, start over. New password: ");
                    $desc->setModuleData('state', self::STATE_NEW_PASSWORD);
                } else {
                    $desc->out("Are you (m)ale or (f)emale? ");
                    $desc->setModuleData('state', self::STATE_SELECT_SEX);
                }
                break;

            case self::STATE_SELECT_SEX:
                $sex = null;
                if (str_starts_with_case($raw, 'm')) {
                    $sex = Sex::Male;
                } elseif (str_starts_with_case($raw, 'f')) {
                    $sex = Sex::Female;
                }

                if (!$sex) {
                    $desc->out("Invalid sex, try again. Are you (m)ale or (f)emale? ");
                } else {
                    $desc->setModuleData('sex', $sex);

                    $desc->outln("Please select your class.");
                    $desc->outln("Available choices: " . PlayerClass::listChoices() . ' or (?) for more information');
                    $desc->out("What is your class? ");
                    $desc->setModuleData('state', self::STATE_SELECT_CLASS);
                }
                break;

            case self::STATE_SELECT_CLASS:
                if (strpos($raw, '?') !== false) {
                    $desc->outln('Description of classes:');
                    $infoText = PlayerClass::infoText();
                    foreach ($infoText as $info) {
                        $desc->outln($info);
                    }
                    $desc->out("What is your class? ");
                } else {
                    $class = PlayerClass::parse($raw);

                    if (!$class) {
                        $desc->out('Invalid class, try again. Which class do you want to play? ');
                    } else {
                        $desc->setModuleData('class', $class);
                        $desc->outln("Ok, you shall be a {$class->value}!");

                        $desc->outln("Please select your physical size.");
                        $desc->outln("Available choices: (t)iny, (s)mall, (m)edium, (l)arge, (h)uge");
                        $desc->out("What is your size? ");
                        $desc->setModuleData('state', self::STATE_SELECT_SIZE);
                    }
                }
                break;

            case self::STATE_SELECT_SIZE:
                $size = null;
                if (str_starts_with_case($raw, 't')) {
                    $size = Size::Tiny;
                } elseif (str_starts_with_case($raw, 's')) {
                    $size = Size::Small;
                } elseif (str_starts_with_case($raw, 'm')) {
                    $size = Size::Medium;
                } elseif (str_starts_with_case($raw, 'l')) {
                    $size = Size::Large;
                } elseif (str_starts_with_case($raw, 'h')) {
                    $size = Size::Huge;
                }

                if ($size) {
                    $desc->setModuleData('size', $size);

                    $desc->out('Are you playing with a smartphone or similar small device? ');
                    $desc->setModuleData('state', self::STATE_SELECT_DEVICE);
                } else {
                    $desc->out("Invalid size, try again. What is your size? ");
                }
                break;

            case self::STATE_SELECT_DEVICE:
                if (str_starts_with_case($raw, 'y')) {
                    $this->enterGame($desc, true, true);
                } elseif (str_starts_with_case($raw, 'n')) {
                    $this->enterGame($desc, true, false);
                } else {
                    $desc->out('Invalid choice, try again. Please answer (y)es or (n)o? ');
                }
                break;

            default:
                throw new RuntimeException("Descriptor in IntroModule is in unknown state.");
        }
    }

    public function prompt(Descriptor $desc): void
    {
        // No prompt
    }

    private function enterGame(Descriptor $desc, bool $new, bool $mobileDevice): void
    {
        if ($new) {
            $player = new Player();
            $player->setName($desc->getModuleData('name'));
            $player->setPassword(password_hash($desc->getModuleData('password'), PASSWORD_DEFAULT));
            $player->setSex($desc->getModuleData('sex'));
            $player->setClass($desc->getModuleData('class'));
            $player->setSize($desc->getModuleData('size'));
            $player->initNewPlayer($mobileDevice);

            Log::info("New player {$player->getName()} entering game from connection #{$desc->getId()}.");
        } else {
            // Search for this character already in game
            $player = $this->lists->findPlayer($desc->getModuleData('player')->getName());

            if ($player) {
                // Character in game: close possible old connection
                $reconnect = true;
                if ($player->getDescriptor()) {
                    Log::info("Player {$player->getName()} taken over by connection #{$desc->getId()}.");
                    $desc->outln("You have taken over character from existing connection.");

                    $player->getDescriptor()->setPlayer(null);
                    $player->getDescriptor()->outln("This character has been taken over by another connection...");
                    $player->getDescriptor()->writeOutput();
                    $player->getDescriptor()->close();
                } else {
                    Log::info("Player {$player->getName()} has reconnected from connection #{$desc->getId()}.");
                    $desc->outln("You have reconnected.");
                }
            } else {
                // Character not in game: use the one loaded from repository
                $reconnect = false;
                $player = $desc->getModuleData('player');
                Log::info("Player {$player->getName()} entering game from connection #{$desc->getId()}.");
            }
        }

        // Attach desc and player
        $desc->setPlayer($player);
        $player->setDescriptor($desc);

        // Enter room
        if (!$player->getRoom()) {
            $startRoom = $this->world->getStartingRoom($player);
            $this->world->livingToRoom($player, $startRoom);
        }

        // Add to global list
        if (!$this->lists->getLiving()->contains($player)) {
            $this->lists->getLiving()->add($player);
        }

        if ($new) {
            // Give him some starting equipment
            foreach (Config::startingEquipment() as $eqInfo) {
                $template = $this->lists->getItemTemplates()->get($eqInfo[0]);

                if (!$template) {
                    Log::error('Unable to load starting equipment, item not found: ' . $eqInfo[0]);
                    continue;
                }

                // Load into equipment slot (if it is free)
                if ($eqInfo[1] && !$player->getEqInSlot($eqInfo[1])) {
                    $this->world->loadItemToEquipment($template, $player, $eqInfo[1]);
                    continue;
                }

                // Load into inventory
                $this->world->loadItemToInventory($template, $player);
            }

            // Store new player to repository
            $this->playerRepo->store($player);

            $this->act->toRoom("New player @t has entered the realm.", true, $player);

            // Welcome message
            $player->outln();
            $player->outpr(
                "Type 'look' to look around and begin to explore your surroundings. " .
                "Type 'help beginner' to read more information about getting started. " .
                'Enjoy your time in the world of ' . Config::gameName() . '!'
            );
        } else {
            if ($reconnect) {
                $this->act->toRoom("@t has reconnected.", true, $player);
            } else {
                // Load inventory and equipment.
                $this->playerItems->loadItems($player);

                $this->act->toRoom("@t has entered the realm.", true, $player);
            }

            // Show room
            $player->outln();
            $player->outln('The world of ' . Config::gameName() . ' materializes before your eyes...');
            $this->render->renderRoom($player, $player->getRoom());

            // Check mail
            $this->mailHandler->readPlayerMail($player);
            foreach ($player->getMail()->getAll() as $mail) {
                if ($mail->getType() == MailType::Unread) {
                    $player->outln('You have unread mail.');
                    break;
                }
            }

            // Ask player to accept rules
            if ($player->getLevel() > 1 && !$player->getAcceptedRules()) {
                $player->outln();
                $player->outpr("Please take a moment to read the rules of the game by typing 'info rules' " .
                    "and then accept them by typing 'info rules accept'. Thanks for playing with us!");
            }
        }

        // Switch to game module
        $desc->setModule($this->game);
    }
}
