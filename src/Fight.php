<?php
/**
 * Gauntlet MUD - Fight handler
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

use Gauntlet\Enum\Attack;
use Gauntlet\Enum\Direction;
use Gauntlet\Enum\ItemFlag;
use Gauntlet\Enum\Modifier;
use Gauntlet\Enum\ScriptType;
use Gauntlet\Template\ContainerTemplate;
use Gauntlet\Util\Lisp;
use Gauntlet\Util\Log;
use Gauntlet\Util\Preferences;
use Gauntlet\Util\Random;

class Fight
{
    private static array $playerNames = [];
    private static array $deathMessages = [];

    public function __construct(
        protected World $world,
        protected ActionMove $actionMove,
        protected Renderer $render,
        protected Act $act
    ) {

    }

    public function initialize(): void
    {
        Log::info('Reading death messages.');

        $filename = DATA_DIR . 'death_messages.yaml';

        if (is_readable($filename)) {
            $data = file_get_contents($filename);

            try {
                self::$deathMessages = Yaml::parse($data);
            } catch (ParseException $ex) {
                Log::error("Unable to parse death messages file: " . $ex->getMessage());
            }
        } else {
            Log::error("Unable to read death messages file: " . $filename);
        }
    }

    public function attack(Living $attacker, Living $victim): void
    {
        // Set the parties as fighting each other
        $this->setTargets($attacker, $victim);

        // Attack multiple times
        for ($i = 0; $i < $attacker->getNumAttacks(); $i++) {
            if ($this->doAttack($attacker, $victim)) {
                // Stop if the victim died
                return;
            }
        }

        // Handle wimpy
        if ($victim->isPlayer()) {
            $wimpy = $victim->getPreference(Preferences::WIMPY, 0);
            if ($wimpy > 0) {
                $healthPercent = ($victim->getHealth() * 100) / $victim->getMaxHealth();
                if ($healthPercent <= $wimpy) {
                    $this->flee($victim);
                }
            }
        }
    }

    public function specialAttack(Living $attacker, Living $victim, float $damage): void
    {
        // Set the parties as fighting each other
        $this->setTargets($attacker, $victim);

        if ($damage > 0) {
            $this->damage($victim, $damage, $attacker);
        }

        // Handle wimpy
        if ($victim->isPlayer()) {
            $wimpy = $victim->getPreference(Preferences::WIMPY, 0);
            if ($wimpy > 0) {
                $healthPercent = ($victim->getHealth() * 100) / $victim->getMaxHealth();
                if ($healthPercent <= $wimpy) {
                    $this->flee($victim);
                }
            }
        }
    }

    public function chanceToHit(Living $attacker, Living $victim): int
    {
        // Base chance to hit
        $toHit = BASE_TO_HIT;

        // Add hit bonus from attacker
        $toHit += $attacker->bonusToHit();

        // Subtract dodge bonus from victim
        $toHit -= $victim->bonusToDodge();

        // Make sure chance to hit is within bounds
        return min(max($toHit, MIN_TO_HIT), MAX_TO_HIT);
    }

    // True if attacker can successfully hit victim
    public function canHit(Living $attacker, Living $victim): bool
    {
        $toHit = $this->chanceToHit($attacker, $victim);

        return Random::percent($toHit);
    }

    // Return true if the victim died, otherwise false
    public function damage(Living $victim, float $amount, ?Living $attacker): bool
    {
        $victim->setHealth($victim->getHealth() - $amount);

        if ($attacker && $attacker->isPlayer()) {
            Experience::gainExperienceFromVictim($attacker, $victim, $amount);
        }

        if ($victim->getHealth() > 0) {
            return false;
        }

        // Evaluate death script
        if ($victim->isMonster()) {
            $script = $victim->getScript(ScriptType::Death);
            if ($script) {
                $scriptResult = Lisp::eval($victim, $script);
                // Truthy value means the death is cancelled
                if ($scriptResult) {
                    // Log warning if the script did not reset health
                    if ($victim->getHealth() <= 0) {
                        Log::warn('Death script did not reset health for monster ' . $victim->getTemplate()->getId());
                    }
                    return false;
                }
            }
        }

        $this->die($victim, $attacker);
        return true;
    }

    public function flee(Living $living): ?Direction
    {
        $fleeCost = 5;

        if ($living->isPlayer()) {
            if ($living->getMove() < $fleeCost) {
                $living->outln('You are too exhausted to flee.');
                return null;
            }

            $living->setMove($living->getMove() - $fleeCost);
        }

        if (Random::percent(30)) {
            $dirs = $living->getRoom()->getExits($living);

            if ($dirs) {
                $dirName = Random::keyFromArray($dirs);
                $dir = Direction::from($dirName);

                if ($this->actionMove->flee($living, $dir)) {
                    $this->act->toChar("You flee " . $dir->name() . "!", $living);
                    return $dir;
                } else {
                    return null;
                }
            }
        }

        $this->act->toChar("You attempt to flee but are unable to escape!", $living);
        $this->act->toRoom('@t attempts to flee but is unable to escape!', true, $living);
        return null;
    }

    public function getAttackDamage(Living $attacker, ?Living $victim): float
    {
        // Get random damage between min/max
        $dmg = Random::floatRange($attacker->getMinDamage(), $attacker->getMaxDamage());

        // Add attackers damage bonus
        $dmg += $attacker->getBonusDamage();

        if ($victim) {
            // Subtract victims armor
            $dmg -= $victim->getMod(Modifier::Armor);
        }

        // Do not allow negative damage, minimum is 1
        return max($dmg, 1);
    }

    public function getBackstabMultiplier(Living $attacker): float
    {
        // Base
        $multip = 3;

        // Bonus from level: 0.04 - 2
        $multip += $attacker->getLevel() / 25;

        // Bonus from dexterity: 0 - 10
        $multip += $attacker->getDex(false) * 0.2;

        return $multip;
    }

    // Return true if the victim died, otherwise false
    private function doAttack(Living $attacker, Living $victim): bool
    {
        if ($this->canHit($attacker, $victim)) {
            $dmg = $this->getAttackDamage($attacker, $victim);
            $this->displayAttackMessage($attacker, $victim, $dmg);
            return $this->damage($victim, $dmg, $attacker);
        }

        // Miss
        $this->displayAttackMessage($attacker, $victim, 0);
        return false;
    }

    private function die(Living $target, ?Living $attacker): void
    {
        $this->act->toChar('You are dead!', $target);
        $this->act->toRoom('@t is dead!', true, $target);

        $this->makeCorpse($target);

        if ($target->isPlayer()) {
            if ($attacker) {
                Log::info($target->getName() . ' was killed by ' . $attacker->getName() .
                    ' in room ' . $target->getRoom()->getTemplate()->getId() . '.');
            } else {
                Log::info($target->getName() . ' was killed in room ' .
                    $target->getRoom()->getTemplate()->getId() . '.');
            }

            $target->setHealth(1);
            $target->setMana(1);
            $target->setMove(1);

            $target->clearAffections();

            // Players are resurrected at starting room
            $startRoom = $this->world->getStartingRoom($target);
            $this->world->livingToRoom($target, $startRoom);

            $this->act->toRoom('@a appears in a flash of bright light.', true, $target);

            // Render room
            $this->render->renderRoom($target, $target->getRoom());
        } else {
            if ($attacker) {
                Log::debug($target->getName() . ' was killed by ' . $attacker->getName() .
                    ' in room ' . $target->getRoom()->getTemplate()->getId() . '.');
            } else {
                Log::debug($target->getName() . ' was killed in room ' .
                    $target->getRoom()->getTemplate()->getId() . '.');
            }

            // Monsters are removed from game
            $this->world->extractLiving($target);
        }
    }

    private function displayAttackMessage(Living $attacker, Living $victim, float $dmg): void
    {
        $msg = null;

        // Miss
        if ($dmg <= 0) {
            $msg = $this->getMissMessage($attacker, $victim);
        }

        // Death message
        if (!$msg && $dmg >= $victim->getHealth() && Random::percent(25)) {
            $msg = $this->getDeathMessage($attacker, $victim);
        }

        // Default message
        if (!$msg) {
            $msg = $this->getDefaultMessage($attacker, $victim, $dmg);
        }

        $this->act->toChar($msg[0], $attacker, $attacker->getWeapon(), $victim);
        $this->act->toVict($msg[1], false, $attacker, $attacker->getWeapon(), $victim);
        $this->act->toRoom($msg[2], false, $attacker, $attacker->getWeapon(), $victim, true);
    }

    private function getDeathMessage(Living $attacker, Living $victim): ?array
    {
        $att = $attacker->getAttackType()->value;

        return Random::fromArray(self::$deathMessages[$att] ?? []);
    }

    private function getDefaultMessage(Living $attacker, Living $victim, float $damage): array
    {
        $attName = $attacker->getAttackType()->fightMessage();

        static $messages = [
            1 => [
                // 1
                'You tickle @T with your #1.',
                '@t tickles you with @s #1.',
                '@t tickles @T with @s #1.'
            ],
            2 => [
                // 2
                'You barely scratch @T with your #1.',
                '@t barely scratches you with @s #1.',
                '@t barely scratches @T with @s #1.'
            ],
            4 => [
                // 3..4
                'You barely #2 @T.',
                '@t barely #3 you.',
                '@t barely #3 @T.'
            ],
            6 => [
                // 5..6
                'You #2 @T.',
                '@t #3 you.',
                '@t #3 @T.'
            ],
            9 => [
                // 7..9
                'You #2 @T hard.',
                '@t #3 you hard.',
                '@t #3 @T hard.'
            ],
            12 => [
                // 10..12
                'You #2 @T very hard.',
                '@t #3 you very hard.',
                '@t #3 @T very hard.'
            ],
            16 => [
                // 13..16
                'You #2 @T extremely hard.',
                '@t #3 you extremely hard.',
                '@t #3 @T extremely hard.'
            ],
            20 => [
                // 17..20
                'You #2 @T brutally hard.',
                '@t #3 you brutally hard.',
                '@t #3 @T brutally hard.'
            ],
            28 => [
                // 21..28
                'You massacre @T to small fragments with your powerful #1!',
                '@t massacres you to small fragments with @s powerful #1!',
                '@t massacres @T to small fragments with @s powerful #1!'
            ],
            38 => [
                // 29..38
                'You RAVAGE @T with your deadly #1!',
                '@t RAVAGES you with @s deadly #1!',
                '@t RAVAGES @T with @s deadly #1!'
            ],
            50 => [
                // 39..50
                'You DECIMATE @T with your savage #1!',
                '@t DECIMATES you with @s savage #1!',
                '@t DECIMATES @T with @s savage #1!'
            ],
            64 => [
                // 51..64
                'You PULVERIZE @T with your vicious #1!',
                '@t PULVERIZES you with @s vicious #1!',
                '@t PULVERIZES @T with @s vicious #1!'
            ],
            80 => [
                // 65..80
                'You OBLITERATE @T with your ferocious #1!!',
                '@t OBLITERATES you with @s ferocious #1!!',
                '@t OBLITERATES @T with @s ferocious #1!!'
            ],
            98 => [
                // 81..98
                'You ANNIHILATE @T with your devastating #1!!',
                '@t ANNIHILATES you with @s devastating #1!!',
                '@t ANNIHILATES @T with @s devastating #1!!'
            ],
            1_000_000 => [
                // 99 ->
                'You ATOMIZE @T with your unparalleled #1!!',
                '@t ATOMIZES you with @s unparalleled #1!!',
                '@t ATOMIZES @T with @s unparalleled #1!!'
            ],
        ];

        // Replace attack name into messages
        $fn = function ($a) use ($attName) {
            for ($i = 1; $i <= 3; $i++) {
                $a = str_replace("#$i", $attName[$i - 1], $a);
            }
            return $a;
        };

        foreach ($messages as $max => $msg) {
            if ($damage <= $max) {
                return array_map($fn, $msg);
            }
        }

        // should not happen
        return null;
    }

    private function getMissMessage(Living $attacker, Living $victim): array
    {
        $attName = $attacker->getAttackType()->fightMessage();

        return [
            sprintf('You miss @T with your %s.', $attName[0]),
            sprintf('@t misses you with @s %s.', $attName[0]),
            sprintf('@t misses @T with @s %s.', $attName[0]),
        ];
    }

    private function makeCorpse(Living $victim): Item
    {
        $template = new ContainerTemplate();

        // Set special id so corpses from same entity are grouped together
        if ($victim->isPlayer()) {
            $template->setName('corpse of ' . $victim->getName());
            $template->setPlural('corpses of ' . $victim->getName());
            $template->setKeywords(['corpse', 'player', $victim->getName()]);
            $template->setShortDesc('A corpse of ' . $victim->getName() . ' is rotting away here.');
            $template->addFlag(ItemFlag::PlayerCorpse);
        } else {
            $template->setName('corpse of ' . $victim->getTemplate()->getAName());
            $template->setPlural('corpses of ' . $victim->getTemplate()->getName());
            $template->setKeywords(array_merge(['corpse'], $victim->getTemplate()->getKeywords()));
            $template->setShortDesc('A corpse of ' . $victim->getTemplate()->getAName() . ' is rotting away here.');
            $template->addFlag(ItemFlag::MonsterCorpse);
        }

        $template->setId($this->getCorpseTemplateId($victim));
        $template->setArticle('a');
        $template->setWeight($victim->getWeight());
        $template->setCapacity(-1); // unlimited, for now

        $corpse = $this->world->loadItemToRoom($template, $victim->getRoom());

        while (!$victim->getEquipment()->empty()) {
            $this->world->itemToContainer($victim->getEquipment()->first(), $corpse);
        }
        while (!$victim->getInventory()->empty()) {
            $this->world->itemToContainer($victim->getInventory()->first(), $corpse);
        }

        return $corpse;
    }

    private function getCorpseTemplateId(Living $living): int
    {
        if ($living->isPlayer()) {
            $index = array_search($living->getName(), self::$playerNames);

            if ($index === false) {
                self::$playerNames[] = $living->getName();
                $index = count(self::$playerNames) - 1;
            }

            // Large enough not to overlap with monster template IDs
            return -($index + 100_000);
        } else {
            return -$living->getTemplate()->getId();
        }
    }

    private function setTargets(Living $attacker, Living $victim): void
    {
        if (!$attacker->getTarget()) {
            $attacker->setTarget($victim);
        }
        if (!$victim->getTarget()) {
            $victim->setTarget($attacker);
        }
    }
}
