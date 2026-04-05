<?php

namespace App\Engine\Battle\Engine\Objects;

use App\Engine\Battle\Engine\Exception;
use App\Engine\Battle\Engine\Models\ShipType;

class PhysicShot
{
	private ShipType $shipType;
	private $damage;
	private $count;

	private int $assorbedDamage = 0;
	private int $bouncedDamage = 0;
	private int $hullDamage = 0;

	public function __construct(ShipType $shipType, int $damage, int $count)
	{
		log_var('damage', $damage);
		log_var('count', $count);

		if ($damage < 0) {
			throw new Exception('Negative damage');
		}
		if ($count < 0) {
			throw new Exception('Negative amount of shots');
		}

		$this->shipType = $shipType->cloneMe();
		$this->damage = $damage;
		$this->count = $count;
	}

	/**
	 * Return the damage assorbed by shield
	 */
	public function getAssorbedDamage(): int
	{
		return $this->assorbedDamage;
	}

	/**
	 * Return the bounced damage
	 */
	public function getBouncedDamage(): int
	{
		return $this->bouncedDamage;
	}

	/**
	 * Return the damage assorbed by hull
	 */
	public function getHullDamage(): int
	{
		return $this->hullDamage;
	}

	/**
	 * Return the total amount of damage from enemy
	 */
	public function getPureDamage(): int
	{
		return $this->damage * $this->count;
	}

	/**
	 * Return the number of hitten ships.
	 */
	public function getHitShips(): int
	{
		return min($this->count, $this->shipType->getCount());
	}

	/**
	 * Start the system
	 */
	public function start(): void
	{
		$this->bounce();
		$this->assorb();
		$this->inflict();
	}

	/**
	 * If the shield is disabled, then bounced damaged is zero.
	 * If the damage is exactly a multipler of the needed to destroy one shield's cell then bounced damage is zero.
	 * If damage is more than shield,then bounced damage is zero.
	 */
	private function bounce(): void
	{
		$count = $this->count;
		$damage = $this->damage;
		$shieldCellValue = $this->shipType->getShieldCellValue();
		$unbauncedDamage = $this->clamp($damage, $shieldCellValue);
		$this->bouncedDamage = ($damage - $unbauncedDamage) * $count;
	}

	/**
	 * If the shield is disabled, then assorbed damaged is zero.
	 * If the total damage is more than shield, than the assorbed damage should equal the shield value.
	 */
	private function assorb(): void
	{
		$count = $this->count;
		$damage = $this->damage;
		$shieldCellValue = $this->shipType->getShieldCellValue();
		$unbauncedDamage = $this->clamp($damage, $shieldCellValue);
		$currentShield = $this->shipType->getCurrentShield();

		if (config('battle.USE_HITSHIP_LIMITATION')) {
			$currentShield = $currentShield * $this->getHitShips() / $this->shipType->getCount();
		}

		$this->assorbedDamage = min($unbauncedDamage * $count, $currentShield);
	}

	/**
	 * HullDamage should be more than zero and less than shiplife.
	 * Expecially, it should be less than the life of hitten ships.
	 */
	private function inflict(): void
	{
		$hullDamage = $this->getPureDamage() - $this->assorbedDamage - $this->bouncedDamage;
		$hullDamage = min($hullDamage, $this->shipType->getCurrentLife() * $this->getHitShips() / $this->shipType->getCount());

		$this->hullDamage = max(0, $hullDamage);
	}

	/**
	 * Return $a if greater than $b, zero otherwise
	 */
	private function clamp($a, $b)
	{
		if ($a > $b) {
			return $a;
		}

		return 0;
	}
}
