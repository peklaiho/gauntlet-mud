<?php
/**
 * Gauntlet MUD - Display formatted output
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

class Act
{
    public function toChar(string $txt, Living $ch, ?Item $obj = null, $victObj = null): void
    {
        $this->performAct($txt, $ch, $obj, $victObj, $ch);
    }

    public function toRoom(string $txt, bool $hide, Living $ch, ?Item $obj = null, null|string|Item|Living $victObj = null, bool $hideVict = false): void
    {
        $this->toList($txt, $hide, $ch->getRoom()->getLiving(), $ch, $obj, $victObj, $hideVict);
    }

    public function toList(string $txt, bool $hide, Collection $list, Living $ch, ?Item $obj = null, null|string|Item|Living $victObj = null, bool $hideVict = false): void
    {
        foreach ($list->getAll() as $target) {
            if ($ch === $target) {
                continue;
            }
            if ($hide && !$target->canSee($ch)) {
                continue;
            }
            if ($hideVict && $target === $victObj) {
                continue;
            }

            $this->performAct($txt, $ch, $obj, $victObj, $target);
        }
    }

    public function toVict(string $txt, bool $hide, Living $ch, ?Item $obj, Living $victObj): void
    {
        if (!$hide || $victObj->canSee($ch)) {
            $this->performAct($txt, $ch, $obj, $victObj, $victObj);
        }
    }

    public function performAct(string $txt, Living $ch, ?Item $obj, null|string|Item|Living $victObj, Living $to): void
    {
        if ($to->isMonster()) {
            return;
        }

        $txt = str_replace('@a', $to->canSee($ch) ? $this->getLivingName($ch, false) : 'someone', $txt);
        $txt = str_replace('@t', $to->canSee($ch) ? $this->getLivingName($ch, true) : 'someone', $txt);
        $txt = str_replace('@x', $to->canSee($ch) ? $this->getPluralName($ch) : 'persons', $txt); // plural
        // $txt = str_replace('@n', $to->canSee($ch) ? $ch->getName() : 'someone', $txt); // no article
        $txt = str_replace('@e', $ch->getSex()->heShe(), $txt);
        $txt = str_replace('@s', $ch->getSex()->hisHer(), $txt);
        $txt = str_replace('@m', $ch->getSex()->himHer(), $txt);

        if ($obj) {
            $txt = str_replace('@o', $to->canSeeItem($obj) ? $obj->getTemplate()->getAName() : 'something', $txt);
            $txt = str_replace('@p', $to->canSeeItem($obj) ? $obj->getTemplate()->getTheName() : 'something', $txt);
            $txt = str_replace('@i', $to->canSeeItem($obj) ? $obj->getTemplate()->getName() : 'something', $txt); // no article
        }

        if ($victObj) {
            if ($victObj instanceof Living) {
                $txt = str_replace('@A', $to->canSee($victObj) ? $this->getLivingName($victObj, false) : 'someone', $txt);
                $txt = str_replace('@T', $to->canSee($victObj) ? $this->getLivingName($victObj, true) : 'someone', $txt);
                $txt = str_replace('@X', $to->canSee($victObj) ? $this->getPluralName($victObj) : 'persons', $txt); // plural
                // $txt = str_replace('@N', $to->canSee($victObj) ? $victObj->getName() : 'someone', $txt); // no article
                $txt = str_replace('@E', $victObj->getSex()->heShe(), $txt);
                $txt = str_replace('@S', $victObj->getSex()->hisHer(), $txt);
                $txt = str_replace('@M', $victObj->getSex()->himHer(), $txt);
            } elseif ($victObj instanceof Item) {
                $txt = str_replace('@O', $to->canSeeItem($victObj) ? $victObj->getTemplate()->getAName() : 'something', $txt);
                $txt = str_replace('@P', $to->canSeeItem($victObj) ? $victObj->getTemplate()->getTheName() : 'something', $txt);
                $txt = str_replace('@I', $to->canSeeItem($victObj) ? $victObj->getTemplate()->getName() : 'something', $txt); // no article
            } elseif (is_string($victObj)) {
                $txt = str_replace('@+', $victObj, $txt);
            }
        }

        $to->outln(ucfirst($txt));
    }

    private function getLivingName(Living $living, bool $the): string
    {
        if ($living->isPlayer()) {
            return $living->getName();
        }

        if ($the) {
            return $living->getTemplate()->getTheName();
        } else {
            return $living->getTemplate()->getAName();
        }
    }

    private function getPluralName(Living $living): string
    {
        if ($living->isPlayer()) {
            return $living->getName();
        }

        return $living->getTemplate()->getPlural();
    }
}
