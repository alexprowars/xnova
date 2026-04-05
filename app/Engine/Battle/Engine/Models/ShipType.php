<?php

namespace App\Engine\Battle\Engine\Models;

use App\Engine\Battle\Engine\Objects\PhysicShot;
use App\Engine\Battle\Engine\Objects\ShipsCleaner;
use App\Engine\Battle\Engine\Exception;

abstract class ShipType extends Type
{
	private int $originalPower;
	private int $originalShield;

	private int $singleShield;
	private int $singleLife;
	private int $singlePower;

	private int $fullShield = 0;
	private int $fullLife = 0;
	private int $fullPower = 0;

	protected int $currentShield = 0;
	protected int $currentLife = 0;

	private float $weaponsFactor = 0.0;
	private float $shieldsFactor = 0.0;
	private float $armourFactor = 0.0;

	private array $rf;
	protected $lastShots;
	protected $lastShipHit;
	private array $cost;

	public float $repairProb = 0.0;

	public function __construct(int $id, int $count, array $rf, $shield, array $cost, int $power, ?float $weaponsFactor = null, ?float $shieldsFactor = null, ?float $armourFactor = null)
	{
		parent::__construct($id, 0);

		$this->rf = $rf;
		$this->lastShots = 0;
		$this->lastShipHit = 0;
		$this->cost = $cost;

		$this->originalShield = $shield;
		$this->originalPower = $power;

		$this->singleShield = $shield;
		$this->singleLife = config('battle.COST_TO_ARMOUR') * array_sum($cost);
		$this->singlePower = $power;

		$this->increment($count);

		if ($weaponsFactor) {
			$this->setWeaponsFactor($weaponsFactor);
		}

		if ($armourFactor) {
			$this->setArmourFactor($armourFactor);
		}

		if ($shieldsFactor) {
			$this->setShieldsFactor($shieldsFactor);
		}
	}

	abstract public function getType(): string;

	abstract public function getRepairProb(): int;

	public function setWeaponsFactor(?float $value)
	{
		if ($value <= 0) {
			return;
		}

		$diff = $value - $this->weaponsFactor;

		if ($diff < 0) {
			throw new Exception('Trying to decrease tech');
		}

		$this->weaponsFactor = $value;
		$incr = 1 + config('battle.WEAPONS_TECH_INCREMENT_FACTOR') * $diff;

		$this->singlePower = (int) floor($this->singlePower * $incr);
		$this->fullPower = (int) floor($this->fullPower * $incr);
	}

	public function setShieldsFactor(float $value)
	{
		if ($value <= 0) {
			return;
		}

		$diff = $value - $this->shieldsFactor;

		if ($diff < 0) {
			throw new Exception('Trying to decrease tech');
		}

		$this->shieldsFactor = $value;
		$incr = 1 + config('battle.SHIELDS_TECH_INCREMENT_FACTOR') * $diff;

		$this->singleShield = (int) floor($this->singleShield * $incr);
		$this->fullShield = (int) floor($this->fullShield * $incr);
		$this->currentShield = (int) floor($this->currentShield * $incr);
	}

	public function setArmourFactor(float $value)
	{
		if ($value <= 0) {
			return;
		}

		$diff = $value - $this->armourFactor;

		if ($diff < 0) {
			throw new Exception('Trying to decrease tech');
		}

		$this->armourFactor = $value;
		$incr = 1 + config('battle.ARMOUR_TECH_INCREMENT_FACTOR') * $diff;

		$this->singleLife = (int) floor($this->singleLife * $incr);
		$this->fullLife = (int) floor($this->fullLife * $incr);
		$this->currentLife = (int) floor($this->currentLife * $incr);
	}

	/**
	 * Increment the amount of ships of this type.
	 */
	public function increment(int $number, int|float|null $newLife = null, int|float|null $newShield = null): void
	{
		parent::increment($number);

		if ($newLife == null) {
			$newLife = $this->singleLife;
		}

		if ($newShield == null) {
			$newShield = $this->singleShield;
		}

		$this->fullLife += $this->singleLife * $number;
		$this->fullPower += $this->singlePower * $number;
		$this->fullShield += $this->singleShield * $number;

		$this->currentLife += (int) floor($newLife * $number);
		$this->currentShield += (int) floor($newShield * $number);
	}

	/**
	 * Decrement the amount of ships of this type.
	 */
	public function decrement(int $number, int|float|null $remainLife = null, int|float|null $remainShield = null): void
	{
		parent::decrement($number);

		if ($remainLife == null) {
			$remainLife = $this->singleLife;
		}

		if ($remainShield == null) {
			$remainShield = $this->singleShield;
		}

		$this->fullLife -= $this->singleLife * $number;
		$this->fullPower -= $this->singlePower * $number;
		$this->fullShield -= $this->singleShield * $number;

		$this->currentLife -= (int) floor($remainLife * $number);
		$this->currentShield -= (int) floor($remainShield * $number);
	}

	/**
	 * Set the amount of ships of this type.
	 */
	public function setCount(int $number, int|float|null $life = null, int|float|null $shield = null): void
	{
		parent::setCount($number);

		$diff = $number - $this->getCount();
		if ($diff > 0) {
			$this->increment($diff, $life, $shield);
		} elseif ($diff < 0) {
			$this->decrement($diff, $life, $shield);
		}
	}

	public function getCost(): array
	{
		return $this->cost;
	}

	public function getWeaponsFactor(): float
	{
		return $this->weaponsFactor;
	}

	public function getShieldsFactor(): float
	{
		return $this->shieldsFactor;
	}

	public function getArmourFactor(): float
	{
		return $this->armourFactor;
	}

	/**
	 * Get the propability of this shipType to shot again given shipType
	 */
	public function getRfTo(self $other): int
	{
		return $this->rf[$other->getId()] ?? 0;
	}


	/**
	 * Get an array of rapid fire
	 */
	public function getRF(): array
	{
		return $this->rf;
	}

	/**
	 * Get the shield value of a single ship of this type.
	 */
	public function getShield(): int
	{
		return $this->singleShield;
	}

	/**
	 * Get the shield cell value of a single ship of this type.
	 */
	public function getShieldCellValue()
	{
		if ($this->isShieldDisabled()) {
			return 0;
		}

		return ($this->singleShield / config('battle.SHIELD_CELLS'));
	}

	/**
	 * Get the hull value of a single ship of this type.
	 */
	public function getHull(): int
	{
		return $this->singleLife;
	}

	/**
	 * Get the power value of a single ship of this type.
	 */
	public function getPower(): int
	{
		return $this->singlePower;
	}

	/**
	 * Get the current shield value of a all ships of this type.
	 */
	public function getCurrentShield(): int
	{
		return $this->currentShield;
	}

	/**
	 * Get the current hull value of a all ships of this type.
	 */
	public function getCurrentLife(): int
	{
		return $this->currentLife;
	}

	/**
	 * Get the current attack power value of a all ships of this type.
	 */
	public function getCurrentPower(): int
	{
		return $this->fullPower;
	}

	/**
	 * Inflict damage to all ships of this type.
	 */
	public function inflictDamage(int $damage, int $shotsToThisShipType): ?PhysicShot
	{
		if ($shotsToThisShipType == 0) {
			return null;
		}
		if ($shotsToThisShipType < 0) {
			throw new Exception("Negative amount of shotsToThisShipType!");
		}

		log_var('Defender single hull', $this->singleLife);
		log_var('Defender count', $this->getCount());
		log_var('currentShield before', $this->currentShield);
		log_var('currentLife before', $this->currentLife);

		$this->lastShots += $shotsToThisShipType;

		$ps = new PhysicShot($this, $damage, $shotsToThisShipType);
		$ps->start();

		log_var('$ps->getAssorbedDamage()', $ps->getAssorbedDamage());
		$this->currentShield -= $ps->getAssorbedDamage();

		if ($this->currentShield < 0 && $this->currentShield > -config('battle.EPSILON')) {
			log_comment('fixing double number currentshield');
			$this->currentShield = 0;
		}

		$this->currentLife -= $ps->getHullDamage();

		if ($this->currentLife < 0 && $this->currentLife > -config('battle.EPSILON')) {
			log_comment('fixing double number currentlife');
			$this->currentLife = 0;
		}

		log_var('currentShield after', $this->currentShield);
		log_var('currentLife after', $this->currentLife);
		$this->lastShipHit += $ps->getHitShips();
		log_var('lastShipHit after', $this->lastShipHit);
		log_var('lastShots after', $this->lastShots);

		if ($this->currentLife < 0) {
			throw new Exception('Negative currentLife!');
		}
		if ($this->currentShield < 0) {
			throw new Exception('Negative currentShield!');
		}
		if ($this->lastShipHit < 0) {
			throw new Exception('Negative lastShipHit!');
		}

		return $ps;
	}

	/**
	 * Start the task of explosion system.
	 */
	public function cleanShips(): ShipsCleaner
	{
		log_var('lastShipHit after', $this->lastShipHit);
		log_var('lastShots after', $this->lastShots);
		log_var('currentLife before', $this->currentLife);

		$sc = new ShipsCleaner($this, $this->lastShipHit, $this->lastShots);
		$sc->start();

		$this->decrement($sc->getExplodedShips(), $sc->getRemainLife(), 0);
		$this->lastShipHit = 0;
		$this->lastShots = 0;
		log_var('currentLife after', $this->currentLife);
		return $sc;
	}

	/**
	 * Repair all shields.
	 */
	public function repairShields(int $round = 0): void
	{
		if ($round > 4) {
			$round = 4;
		}

		$this->currentShield = (int) ceil($this->fullShield * (1 - 0.25 * $round));
	}

	/**
	 * Return true if the current shield of each ships are almost zero.
	 */
	public function isShieldDisabled(): bool
	{
		return $this->currentShield / $this->getCount() < 0.01;
	}

	public function setRepairProb(float $factor = 0): void
	{
		$this->repairProb = $factor;
	}

	public function cloneMe(): self
	{
		$class = get_class($this);
		$tmp = new $class($this->getId(), $this->getCount(), $this->rf, $this->originalShield, $this->cost, $this->originalPower, $this->weaponsFactor, $this->shieldsFactor, $this->armourFactor);

		$tmp->currentShield = $this->currentShield;
		$tmp->currentLife = $this->currentLife;
		$tmp->lastShots = $this->lastShots;
		$tmp->lastShipHit = $this->lastShipHit;

		return $tmp;
	}
}
