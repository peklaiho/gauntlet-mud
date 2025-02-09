<?php
/**
 * Gauntlet MUD - Player instance
 * Copyright (C) 2017-2025 Pekka Laiho
 * License: AGPL 3.0 (see LICENSE)
 */

namespace Gauntlet;

use Gauntlet\Enum\AdminLevel;
use Gauntlet\Enum\Attack;
use Gauntlet\Enum\Attribute;
use Gauntlet\Enum\Damage;
use Gauntlet\Enum\EqSlot;
use Gauntlet\Enum\Modifier;
use Gauntlet\Enum\PlayerClass;
use Gauntlet\Enum\RoomFlag;
use Gauntlet\Enum\Sex;
use Gauntlet\Enum\Size;
use Gauntlet\Enum\Skill;
use Gauntlet\Module\Editor;
use Gauntlet\Module\Pager;
use Gauntlet\Trait\CarryingCapacity;
use Gauntlet\Trait\CreationTime;
use Gauntlet\Trait\Level;
use Gauntlet\Trait\SexAndSize;
use Gauntlet\Trait\SkillPoints;
use Gauntlet\Trait\TemporaryPlayerItems;
use Gauntlet\Trait\Training;
use Gauntlet\Util\Color;
use Gauntlet\Util\ColorPref;
use Gauntlet\Util\Preferences;
use Gauntlet\Util\StringSplitter;
use Gauntlet\Util\TableFormatter;

class Player extends Living
{
    protected ?Descriptor $descriptor;
    protected ?AdminLevel $admin = null;
    protected int $bank = 0;
    protected int $experience = 0;
    protected string $name;
    protected string $password;
    protected ?string $title = null;
    protected Preferences $preferences;
    protected ColorPref $colorPref;
    protected array $aliases = [];
    protected PlayerClass $class = PlayerClass::Warrior; // Just a default
    protected bool $acceptedRules = false;
    protected Collection $mail;

    // Stats only players have
    protected float $mana;
    protected float $move;

    use CarryingCapacity;
    use CreationTime;
    use Level;
    use SexAndSize;
    use SkillPoints;
    use TemporaryPlayerItems;
    use Training;

    public function __construct()
    {
        parent::__construct();

        $this->preferences = new Preferences();
        $this->colorPref = new ColorPref();
        $this->mail = new Collection();
    }

    public function colorize(string $txt, string $key): string
    {
        if ($this->preferences->get(Preferences::COLOR)) {
            $start = Color::getCode($this->colorPref->get($key));
            $end = Color::getCode(Color::RESET);

            return $start . $txt . $end;
        } else {
            return $txt;
        }
    }

    public function colorizeStatic(string $txt, string $color): string
    {
        if ($this->preferences->get(Preferences::COLOR)) {
            $start = Color::getCode($color);
            $end = Color::getCode(Color::RESET);

            return $start . $txt . $end;
        } else {
            return $txt;
        }
    }

    public function highlight(string $txt, ?string $endKey = null): string
    {
        if ($this->preferences->get(Preferences::COLOR)) {
            $start = Color::getCode($this->colorPref->get(ColorPref::HIGHLIGHT));
            if ($endKey) {
                $end = Color::getCode($this->colorPref->get($endKey));
            } else {
                $end = Color::getCode(Color::RESET);
            }

            $txt = str_replace('{', $start, $txt);
            $txt = str_replace('}', $end, $txt);
        } else {
            // Remove tags if no color
            $txt = str_replace('{', '', $txt);
            $txt = str_replace('}', '', $txt);
        }

        return $txt;
    }

    // Can start violence in room?
    public function checkInitiateViolence(bool $message): bool
    {
        if ($this->getTarget()) {
            if ($message) {
                $this->outln("You are already fighting!");
            }

            return false;
        } elseif ($this->getRoom()->getTemplate()->hasFlag(RoomFlag::Peaceful)) {
            if ($message) {
                $this->outln("You have such a peaceful feeling here.");
            }

            return false;
        } elseif ($this->isEncumbered()) {
            if ($message) {
                $this->outln('You are too encumbered to fight.');
            }

            return false;
        }

        return true;
    }

    // Can start violence against $target?
    public function checkInitiateViolenceAgainst(Living $target, bool $message): bool
    {
        if ($this === $target) {
            return true;
        }

        if ($target->isPlayer() && $target->getAdminLevel()) {
            $targetLevel = $target->getAdminLevel()->value;
            $ownLevel = $this->getAdminLevel() ? $this->getAdminLevel()->value : 0;

            if ($targetLevel >= $ownLevel) {
                if ($message) {
                    $this->outln('That is not a good idea!');
                }

                return false;
            }
        }

        return true;
    }

    #[\Override]
    public function canSee(Living $other): bool
    {
        if ($this->canSeeRoom()) {
            return true;
        }

        // Can always see ourselves
        if ($other === $this) {
            return true;
        }

        // No, cannot see in dark
        return false;
    }

    #[\Override]
    public function canSeeItem(Item $item): bool
    {
        if ($this->canSeeRoom()) {
            return true;
        }

        // Otherwise we can only see items carried or worn by us
        return $item->getCarrier() === $this || $item->getWearer() === $this;
    }

    #[\Override]
    public function canSeeRoom(): bool
    {
        // Room is lit or carrying a light source
        return !$this->getRoom()->isDark() || $this->hasLight();
    }

    public function hasLight(): bool
    {
        // Admins have permanent light in preferences
        if ($this->getAdminLevel() && $this->preferences->get(Preferences::PERMLIGHT)) {
            return true;
        }

        // For now just wearing any item in light slot is enough
        if ($this->getEqInSlot(EqSlot::Light)) {
            return true;
        }

        // No light
        return false;
    }

    public function hasSkill(Skill $skill): bool
    {
        // Immortals have all skills
        if ($this->getAdminLevel()) {
            return true;
        }

        $skills = SkillMap::getSkills($this->getClass());

        foreach ($skills as $skillInfo) {
            if ($this->getLevel() >= $skillInfo[0] && $skill == $skillInfo[1] &&
                (!Config::useSkillPoints() || $this->getSkillLevel($skill) > 0)) {
                return true;
            }
        }

        return false;
    }

    #[\Override]
    public function shouldFollow(Living $other): bool
    {
        // Check pref
        if (!$this->preferences->get(Preferences::FOLLOW)) {
            return false;
        }

        // Follow the leader of the group
        if ($this->getGroup() && $this->getGroup()->getLeader() === $other) {
            return true;
        }

        return false;
    }

    public function getAliases(): array
    {
        return $this->aliases;
    }

    public function getAcceptedRules(): bool
    {
        return $this->acceptedRules;
    }

    public function getDescriptor(): ?Descriptor
    {
        return $this->descriptor;
    }

    public function getBank(): int
    {
        return $this->bank;
    }

    public function getClass(): PlayerClass
    {
        return $this->class;
    }

    #[\Override]
    public function getExperience(): int
    {
        return $this->experience;
    }

    #[\Override]
    public function getMaxHealth(): float
    {
        // Base value
        $value = 40;

        // Bonus from level: 10 - 500
        $value += $this->getLevel() * 10;

        // Bonus from extra constitution
        $value += $this->getCon(false) * 20;

        // Bonus from equipment
        $value += $this->getMod(Modifier::Health);

        // Minimum 10
        return max($value, 10);
    }

    public function getMaxMana(): float
    {
        // Base value
        $value = 40;

        // Bonus from level: 10 - 500
        $value += $this->getLevel() * 10;

        // Bonus from intelligence
        $value += $this->getInt(false) * 20;

        // Bonus from equipment
        $value += $this->getMod(Modifier::Mana);

        // Minimum 10
        return max($value, 10);
    }

    public function getMaxMove(): float
    {
        // Base value
        $value = 98;

        // Bonus from level: 2 - 100
        $value += $this->getLevel() * 2;

        // Bonus from equipment
        $value += $this->getMod(Modifier::Move);

        // Minimum 10
        return max($value, 10);
    }

    public function getMana(): float
    {
        return $this->mana;
    }

    public function getMove(): float
    {
        return $this->move;
    }

    #[\Override]
    public function getName(): string
    {
        return $this->name;
    }

    public function getNameWithState(): string
    {
        if (!$this->descriptor) {
            return $this->getName() . ' (disconnected)';
        } elseif ($this->descriptor->getModule() instanceof Editor) {
            return $this->getName() . ' (writing)';
        } else {
            return $this->getName();
        }
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getPreference(string $key, $defaultValue = null)
    {
        return $this->getPreferences()->get($key, $defaultValue);
    }

    public function getPreferences(): Preferences
    {
        return $this->preferences;
    }

    public function getColorPref(): ColorPref
    {
        return $this->colorPref;
    }

    public function getMail(): Collection
    {
        return $this->mail;
    }

    #[\Override]
    public function getWeight(): float
    {
        return $this->getSize()->getPlayerWeight($this->getSex());
    }

    public function initNewPlayer(bool $mobileDevice): void
    {
        $this->preferences->set(Preferences::COLOR, true);
        $this->preferences->set(Preferences::ECHO, true);

        if ($mobileDevice) {
            // Do not limit line length on mobile devices
            $this->preferences->set(Preferences::LINE_LENGTH, 0);
        } else {
            $this->preferences->set(Preferences::LINE_LENGTH, 80);
        }

        $this->setHealth($this->getMaxHealth());
        $this->setMana($this->getMaxMana());
        $this->setMove($this->getMaxMove());

        $this->setCoins(250);

        $this->setCreationTime(time());
    }

    public function getAdminLevel(): ?AdminLevel
    {
        return $this->admin;
    }

    public function out(string $txt, ...$args): void
    {
        if ($this->descriptor) {
            if ($args) {
                $txt = sprintf($txt, ...$args);
            }
            $this->descriptor->out($txt);
        }
    }

    public function outln(?string $txt = null, ...$args): void
    {
        if ($this->descriptor) {
            if ($args) {
                $txt = sprintf($txt, ...$args);
            }
            $this->descriptor->outln($txt);
        }
    }

    public function outpr(string $txt, bool $usePager = false): void
    {
        $lines = StringSplitter::splitByNewline($txt);

        // Split long lines if preferred
        $lineWidth = $this->getPreference(Preferences::LINE_LENGTH, 80);
        if ($lineWidth > 0) {
            $tmp = [];
            foreach ($lines as $line) {
                foreach (StringSplitter::paragraph($line, $lineWidth) as $line2) {
                    $tmp[] = $line2;
                }
            }
            $lines = $tmp;
        }

        if ($usePager) {
            $pageLength = $this->getPreference(Preferences::PAGE_LENGTH, 0);
            if ($pageLength > 0 && count($lines) > $pageLength) {
                $pager = SERVICE_CONTAINER->get(Pager::class);
                $this->descriptor->setModule($pager, [
                    'pages' => array_chunk($lines, $pageLength)
                ]);
                return;
            }
        }

        foreach ($lines as $line) {
            $this->outln($line);
        }
    }

    public function outWordTable(array $words): void
    {
        $lineWidth = $this->getPreference(Preferences::LINE_LENGTH, 80);

        if ($lineWidth > 0) {
            $table = TableFormatter::formatWordList($words, $lineWidth);

            foreach ($table as $line) {
                $this->outln($line);
            }
        } else {
            $this->outln(implode('  ', $words));
        }
    }

    #[\Override]
    public function regenerate(): void
    {
        // This is called every second (UPDATE_LIVING in bootstrap.php)

        $multip = 1;

        // Faster regen for flagged rooms
        if ($this->getRoom()->getTemplate()->hasFlag(RoomFlag::Regen)) {
            $multip += 0.5;
        }

        // Slower regen if encumbered
        if ($this->isEncumbered()) {
            $multip -= 0.5;
        }

        $fn = fn ($cur, $max, $div) => min($max / $div, $max - $cur);

        // Health is full in 5 minutes
        $inc = $fn($this->health, $this->getMaxHealth(), 300 / $multip);
        if ($inc > 0) {
            $this->health += $inc;
        }

        // Mana is full in 10 minutes
        $inc = $fn($this->mana, $this->getMaxMana(), 600 / $multip);
        if ($inc > 0) {
            $this->mana += $inc;
        }

        // Move is full in 5 minutes
        $inc = $fn($this->move, $this->getMaxMove(), 300 / $multip);
        if ($inc > 0) {
            $this->move += $inc;
        }
    }

    public function setAliases(array $val): void
    {
        $this->aliases = $val;
    }

    public function setAcceptedRules(bool $val): void
    {
        $this->acceptedRules = $val;
    }

    public function setDescriptor(?Descriptor $val): void
    {
        $this->descriptor = $val;
    }

    public function setAdminLevel(?AdminLevel $val): void
    {
        $this->admin = $val;
    }

    public function addBank(int $val): int
    {
        $this->bank += $val;
        return $this->bank;
    }

    public function setBank(int $val): void
    {
        $this->bank = $val;
    }

    public function setClass(PlayerClass $val): void
    {
        $this->class = $val;
    }

    public function addExperience(int $val): void
    {
        // Make sure we don't go negative
        $this->experience = max(0, $this->experience + $val);
    }

    public function setExperience(int $val): void
    {
        $this->experience = $val;
    }

    public function setMana(float $val): void
    {
        $this->mana = $val;
    }

    public function setMove(float $val): void
    {
        $this->move = $val;
    }

    public function setName(string $val): void
    {
        $this->name = $val;
    }

    public function setPassword(string $val): void
    {
        $this->password = $val;
    }

    public function setTitle(?string $val): void
    {
        $this->title = $val;
    }

    // Attacks and damage

    #[\Override]
    public function getAttackType(): Attack
    {
        $weapon = $this->getWeapon();

        if ($weapon) {
            return $weapon->getTemplate()->getAttackType();
        }

        return Attack::Hit;
    }

    #[\Override]
    public function getDamageType(): Damage
    {
        $weapon = $this->getWeapon();

        if ($weapon) {
            return $weapon->getTemplate()->getDamageType();
        }

        return Damage::Physical;
    }

    #[\Override]
    public function getNumAttacks(): int
    {
        $attacks = 1;

        if ($this->hasSkill(Skill::SecondAttack)) {
            $attacks++;
        }

        if ($this->hasSkill(Skill::ThirdAttack)) {
            $attacks++;
        }

        return $attacks;
    }

    #[\Override]
    public function getMinDamage(): float
    {
        $weapon = $this->getWeapon();

        if ($weapon) {
            return $weapon->getTemplate()->getMinDamage();
        }

        return 2;
    }

    #[\Override]
    public function getMaxDamage(): float
    {
        $weapon = $this->getWeapon();

        if ($weapon) {
            return $weapon->getTemplate()->getMaxDamage();
        }

        return 3;
    }

    // Override attributes to account for training

    #[\Override]
    public function getStr(bool $includeBase = true): int
    {
        return parent::getStr($includeBase) + $this->getTrainedAttribute(Attribute::Strength);
    }

    #[\Override]
    public function getDex(bool $includeBase = true): int
    {
        return parent::getDex($includeBase) + $this->getTrainedAttribute(Attribute::Dexterity);
    }

    #[\Override]
    public function getInt(bool $includeBase = true): int
    {
        return parent::getInt($includeBase) + $this->getTrainedAttribute(Attribute::Intelligence);
    }

    #[\Override]
    public function getCon(bool $includeBase = true): int
    {
        return parent::getCon($includeBase) + $this->getTrainedAttribute(Attribute::Constitution);
    }

    #[\Override]
    public function getTechnicalName(): string
    {
        return "Player<{$this->name}>";
    }
}
