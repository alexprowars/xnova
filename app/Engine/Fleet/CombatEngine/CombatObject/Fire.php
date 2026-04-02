<?php

namespace App\Engine\Fleet\CombatEngine\CombatObject;

use App\Engine\Fleet\CombatEngine\Models\Fleet;
use App\Engine\Fleet\CombatEngine\Models\ShipType;
use App\Engine\Fleet\CombatEngine\Utils\Gauss;
use App\Engine\Fleet\CombatEngine\Utils\GeometricDistribution;
use App\Engine\Fleet\CombatEngine\Utils\Math;
use App\Engine\Fleet\CombatEngine\Utils\Number;

class Fire
{
	private ShipType $attackerShipType;
	private Fleet $defenderFleet;

	private int $shots = 0;
	private int $power = 0;

	public function __construct(ShipType $attackerShipType, Fleet $defenderFleet)
	{
		log_comment('calculating fire from attacker ' . $attackerShipType->getId());

		$this->attackerShipType = $attackerShipType;
		$this->defenderFleet = $defenderFleet;
		$this->calculateTotal();
	}

	public function getPower(): int
	{
		return $this->attackerShipType->getPower();
	}

	public function getId()
	{
		return $this->attackerShipType->getId();
	}

	public function getAttackerTotalFire(): int
	{
		return $this->power;
	}

	public function getAttackerTotalShots(): int
	{
		return $this->shots;
	}

	private function calculateTotal(): void
	{
		$this->shots += $this->attackerShipType->getCount();
		$this->power += $this->getNormalPower();

		if (config('battle.USE_RF')) {
			$this->calculateRf();
		}

		log_var('$this->shots', $this->shots);
	}

	private function calculateRf(): void
	{
		$tmpshots = (int) round($this->getShotsFromOneAttackerShipOfType($this->attackerShipType) * $this->attackerShipType->getCount());

		log_var('$tmpshots', $tmpshots);

		$this->power += $tmpshots * $this->attackerShipType->getPower();
		$this->shots += $tmpshots;
	}

	/**
	 * This function return the number of shots caused by RF from one ShipType to all defenders
	 */
	private function getShotsFromOneAttackerShipOfType(ShipType $shipType_A): float
	{
		$p = $this->getProbabilityToShotAgainForAttackerShipOfType($shipType_A);
		$meanShots = GeometricDistribution::getMeanFromProbability(1 - $p) - 1;

		if (config('battle.USE_RANDOMIC_RF')) {
			$max = $meanShots * (1 + config('battle.MAX_RF_BUFF'));
			$min = $meanShots * (1 - config('battle.MAX_RF_NERF'));

			log_var('$max', $max);
			log_var('$min', $min);
			log_var('$mean', $meanShots);

			return Gauss::getNextMsBetween($meanShots, GeometricDistribution::getStandardDeviationFromProbability(1 - $p), $min, $max);
		}

		return $meanShots;
	}

	/**
	 * This function return the probability of a ShipType to shot thanks RF
	 */
	private function getProbabilityToShotAgainForAttackerShipOfType(ShipType $shipType_A): int
	{
		$p = 0;

		foreach ($this->defenderFleet->getIterator() as $shipType_D) {
			$RF = $shipType_A->getRfTo($shipType_D);
			$probabilityToShotAgain = 1 - GeometricDistribution::getProbabilityFromMean($RF);
			$probabilityToHitThisType = $shipType_D->getCount() / $this->defenderFleet->getTotalCount();
			$p += $probabilityToShotAgain * $probabilityToHitThisType;
		}

		return $p;
	}

	/**
	 * Return the total fire shotted from attacker ShipType to all defenders without RF
	 */
	private function getNormalPower(): int
	{
		return $this->attackerShipType->getCount() * $this->attackerShipType->getPower();
	}

	//------- INCOMING FIRE------------

	public function getShotsFiredByAttackerTypeToDefenderType(ShipType $shipType_A, ShipType $shipType_D, $real = false): Number
	{
		$first = $this->getShotsFiredByAttackerToOne($shipType_A);
		$second = new Number($shipType_D->getCount());

		return Math::multiple($first, $second, $real);
	}

	public function getShotsFiredByAttackerToOne(ShipType $shipType_A, $real = false): Number
	{
		$num = $this->getShotsFiredByAttackerToAll($shipType_A);
		$denum = new Number($this->defenderFleet->getTotalCount());

		return Math::divide($num, $denum, $real);
	}

	public function getShotsFiredByAllToDefenderType(ShipType $shipType_D, $real = false): Number
	{
		$first = $this->getShotsFiredByAllToOne();
		$second = new Number($shipType_D->getCount());

		return Math::multiple($first, $second, $real);
	}

	public function getShotsFiredByAttackerToAll(ShipType $shipType_A, $real = false): Number
	{
		$num = new Number($this->getAttackerTotalShots() * $shipType_A->getCount());

		$denum = new Number($this->attackerShipType->getCount());

		return Math::divide($num, $denum, $real);
	}

	public function getShotsFiredByAllToOne($real = false): Number
	{
		$num = new Number($this->getAttackerTotalShots());
		$denum = new Number($this->defenderFleet->getTotalCount());

		return Math::divide($num, $denum, $real);
	}

	public function cloneMe(): Fire
	{
		return new Fire($this->attackerShipType, $this->defenderFleet);
	}
}
