<?php

namespace App\Engine\Fleet\CombatEngine\CombatObject;

use App\Engine\Fleet\CombatEngine\Exception;
use App\Engine\Fleet\CombatEngine\Models\ShipType;

class PhysicShot
{
	private $shipType;
	private $damage;
	private $count;

	private $assorbedDamage = 0;
	private $bouncedDamage = 0;
	private $hullDamage = 0;

	public function __construct(ShipType $shipType, int $damage, int $count)
	{
		\log_var('damage', $damage);
		\log_var('count', $count);

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
	 * PhysicShot::getAssorbedDamage()
	 * Return the damage assorbed by shield
	 * @return float
	 */
	public function getAssorbedDamage()
	{
		return $this->assorbedDamage;
	}

	/**
	 * PhysicShot::getBouncedDamage()
	 * Return the bounced damage
	 * @return float
	 */
	public function getBouncedDamage()
	{
		return $this->bouncedDamage;
	}

	/**
	 * PhysicShot::getHullDamage()
	 * Return the damage assorbed by hull
	 * @return float
	 */
	public function getHullDamage()
	{
		return $this->hullDamage;
	}

	/**
	 * PhysicShot::getPureDamage()
	 * Return the total amount of damage from enemy
	 * @return int
	 */
	public function getPureDamage()
	{
		return $this->damage * $this->count;
	}

	/**
	 * PhysicShot::getHitShips()
	 * Return the number of hitten ships.
	 * @return int
	 */
	public function getHitShips()
	{
		return min($this->count, $this->shipType->getCount());
	}

	/**
	 * PhysicShot::start()
	 * Start the system
	 */
	public function start()
	{
		$this->bounce();
		$this->assorb();
		$this->inflict();
	}

	/**
	 * PhysicShot::bounce()
	 * If the shield is disabled, then bounced damaged is zero.
	 * If the damage is exactly a multipler of the needed to destroy one shield's cell then bounced damage is zero.
	 * If damage is more than shield,then bounced damage is zero.
	 */
	private function bounce()
	{
		$count = $this->count;
		$damage = $this->damage;
		$shieldCellValue = $this->shipType->getShieldCellValue();
		$unbauncedDamage = $this->clamp($damage, $shieldCellValue);
		$this->bouncedDamage = ($damage - $unbauncedDamage) * $count;
	}

	/**
	 * PhysicShot::assorb()
	 * If the shield is disabled, then assorbed damaged is zero.
	 * If the total damage is more than shield, than the assorbed damage should equal the shield value.
	 */
	private function assorb()
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
	 * PhysicShot::inflict()
	 * HullDamage should be more than zero and less than shiplife.
	 * Expecially, it should be less than the life of hitten ships.
	 */
	private function inflict()
	{
		$hullDamage = $this->getPureDamage() - $this->assorbedDamage - $this->bouncedDamage;
		$hullDamage = min($hullDamage, $this->shipType->getCurrentLife() * $this->getHitShips() / $this->shipType->getCount());
		$this->hullDamage = max(0, $hullDamage);
	}

	/**
	 * PhysicShot2::clamp()
	 * Return $a if greater than $b, zero otherwise
	 * @param mixed $a
	 * @param mixed $b
	 * @return mixed
	 */
	private function clamp($a, $b)
	{
		if ($a > $b) {
			return $a;
		}

		return 0;
	}
}
