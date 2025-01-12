<?php
/**
 * Gauntlet MUD - Socials
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

use Gauntlet\Player;
use Gauntlet\Util\Input;
use Gauntlet\Util\LivingFinder;
use Gauntlet\Util\Log;

class Socials
{
    protected string $filename;
    protected array $data = [];
    protected string $missing = '(missing message in socials file)';

    public function __construct(
        protected Act $act
    ) {
        $this->filename = DATA_DIR . 'socials.yaml';
    }

    public function list(): array
    {
        return array_keys($this->data);
    }

    public function initialize(): void
    {
        Log::info('Reading socials.');

        if (is_readable($this->filename)) {
            $data = file_get_contents($this->filename);

            try {
                $this->data = Yaml::parse($data);
                Log::info(count($this->data) .  ' socials read.');
            } catch (ParseException $ex) {
                Log::error("Unable to parse socials file: " . $ex->getMessage());
            }
        } else {
            Log::error("Unable to read socials file: " . $this->filename);
        }
    }

    public function parse(Player $player, Input $input): bool
    {
        $social = $this->findSocial($input->getCommand());

        if (!$social) {
            return false;
        }

        if ($input->empty()) {
            $this->actSocial($player, $social);
        } else {
            $lists = [$player->getRoom()->getLiving()];
            $target = (new LivingFinder($player, $lists))
                ->excludeSelf()
                ->find($input->get(0));

            if ($target) {
                $this->actSocialTarget($player, $social, $target);
            } else {
                $player->outln(MESSAGE_NOONE);
            }
        }

        return true;
    }

    public function actSocial(Living $living, array $social): void
    {
        $this->act->toChar($social['self'] ?? $this->missing, $living);
        $this->act->toRoom($social['room'] ?? $this->missing, false, $living);
    }

    public function actSocialTarget(Living $living, array $social, Living $target): void
    {
        $this->act->toChar($social['victim_self'] ?? $this->missing, $living, null, $target);
        $this->act->toVict($social['victim'] ?? $this->missing, false, $living, null, $target);
        $this->act->toRoom($social['victim_room'] ?? $this->missing, false, $living, null, $target, true);
    }

    public function findSocial(string $name): ?array
    {
        foreach ($this->data as $key => $val) {
            if (str_starts_with_case($key, $name)) {
                return $val;
            }
        }

        return null;
    }
}
